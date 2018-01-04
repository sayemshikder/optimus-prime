<?php

namespace App\Http\Controllers;

use App\Allpost;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AllpostController extends Controller
{

    public function __construct()
    {
        \App::setLocale(CoreController::getLang());
    }

    /**
     *  show all posts view
     */
    public function index()
    {
        $posts = \App\Allpost::where('userId', Auth::user()->id)->get();
        return view('allpost', compact('posts'));
    }

    /**
     * @param Request $re
     * delete all post from social media
     */
    public function delFromAll(Request $re)
    {

        FacebookController::fbDel($re->postId);
        FacebookController::fbgDel($re->postId);
        Write::twDel($re->postId);
        WordpressController::wpDel($re->postId);
        Write::tuDel($re->postId);
        Allpost::where('postId', $re->postId)->where('userId', Auth::user()->id)->delete();
        return "Done";

    }

    /**
     * @return string
     */
    public function delAll()
    {
        try {
            Allpost::where('userId', Auth::user()->id)->truncate();
            return "success";
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    /**
     * @param Request $request
     * @return string
     */
    public function delPost(Request $request)
    {
        try {
            Allpost::where('userId', Auth::user()->id)->where('id', $request->id)->delete();
            return "success";
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

    }
}
