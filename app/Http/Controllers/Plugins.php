<?php

namespace App\Http\Controllers;

use App\PluginsRecord;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Nwidart\Modules\Facades\Module;
use PhpParser\Node\Expr\Cast\Object_;
use ZipArchive;


class Plugins extends Controller
{
    public function __construct()
    {
        \App::setLocale(CoreController::getLang());

    }

    public function index()
    {
        if (Auth::user()->type != "admin") {
            return "unauthorized";
        }
        $data = [];
        $modules = glob(base_path('Modules/') . "*");
        foreach ($modules as $module) {
            if (strpos($module, '.') !== false || strpos($module, '__') !== false) {
//                not folder
            } else {
                $content = file_get_contents($module . "/module.json");
                $json = json_decode($content, true);
                array_push($data, $json);
            }


        }

        // return data to view
        return view('pluginList', compact('data'));

    }

    public function action(Request $request)
    {
        if (Auth::user()->type != "admin") {
            return "unauthorized";
        }
        $module = Module::find($request->name);

        try {
            if ($request->action == "enable") {
                $module->enable();
                return "success";
            } elseif ($request->action == "disable") {
                $module->disable();
                return "success";
            } elseif ($request->action == "delete") {

                $module->delete();
                return "success";
            }
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

    }

    public function addPlugin()
    {
        if (Auth::user()->type != "admin") {
            return "unauthorized";
        }

        return view('addPlugin');

    }

    public static function menu($position)
    {


        $data = "";
        $modules = glob(base_path('Modules/') . "*");
        foreach ($modules as $module) {
            if (strpos($module, '.') !== false || strpos($module, '__') !== false) {
//                not folder
            } else {
//                get plugin content

                $moduleJson = file_get_contents($module . "/module.json");
                $json = json_decode($moduleJson, true);


                if (self::check($json['name'],Auth::user()->id)) {
                    $menuPosition = $json['position'];
                    if ($json['active'] == 1) {
                        // if plugin made for admin
                        if ($json['for'] == "admin") {
                            if (Auth::user()->type == "admin") {
                                if ($menuPosition == $position) {
                                    $content = file_get_contents($module . "/menu.php");
                                    $data .= $content;
                                }
                            }
                        } else {
                            if ($menuPosition == $position) {
                                $content = file_get_contents($module . "/menu.php");
                                $data .= $content;
                            }
                        }

                    }
                }


            }


        }

        // return data to view
        return $data;

    }

    public static function is_active($pluginName)
    {

    }

    public function upload(Request $request)
    {
        $file = $request->file('file');

        $fileName = $file->getClientOriginalName();
        $fileType = $file->getClientMimeType();
        if ($fileType == 'application/zip' || $fileType == 'application/zip') {

            try {
                Input::file('file')->move(base_path() . '/Modules/', $fileName);

                $zip = new ZipArchive;
                $res = $zip->open(base_path() . '/Modules/' . $fileName);
                $folderName = str_replace(".zip", "", $fileName);
                if ($res === TRUE) {
                    $zip->extractTo(base_path() . '/Modules/');
                    $zip->close();
                    return response()->json(["status" => "success", "fileName" => $fileName . "." . $file->getClientOriginalExtension()]);
                } else {
                    echo 'error';
                }


            } catch (\Exception $e) {
                echo "error";
            }
        } else {
            echo "invalid File";
        }
    }



    public static function check($pluginName,$userId)
    {
        if (PluginsRecord::where('userId', $userId)->where('pluginName', $pluginName)->exists()) {
            return true;
        }
    }

    public function activePluginForUser(Request $request)
    {
        if ($request->action == "enable") {
            if (!PluginsRecord::where('userId', $request->userId)->where('pluginName', $request->pluginName)->exists()) {
                $record = new PluginsRecord();
                $record->userId = $request->userId;
                $record->pluginName = $request->pluginName;
                $record->save();
                return "success";

            }
        } else {
            try {
                PluginsRecord::where('userId', $request->userId)->where('pluginName', $request->pluginName)->delete();
                return "success";
            } catch (\Exception $exception) {
                return $exception->getMessage();
            }
        }
    }


}
