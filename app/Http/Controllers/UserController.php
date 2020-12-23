<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $user = auth()->user();
        // print($user->id);print($user->name);print($user->email);

        //$user_placeholders = compact('user');
        return view('profile', [
                'user' => $user,
                'a' => 100,
                'fav_coins' => $this->get_favourite_coins()
                ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $user = \App\Models\User::findOrFail($id);

        $user->name = $request->get('name');
        $user->email = $request->get('email');
        $user->b_key = $request->get('b_]key');
        if (!empty($request->get('b_private')))
            $user->b_private = self::ec($request->get('b_private'));
    
    
        $user->save();
    
        return \Redirect::route('profile', [$user->id])->with('message', 'User has been updated!');
    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    // helpers
    static public function ec($c) {
        return str_replace('o', '%$£@!', $c);
    }
    static public function dc($c) {
        return str_replace('%$£@!', 'o', $c);
    }

    public static function get_favourite_coins($as = 'string') {
        $user = auth()->user();
        $fav_coins_array = unserialize($user->fav_coins);
        if ($as === 'string')
            return implode(', ', $fav_coins_array);
        return $fav_coins_array;
    }

}
