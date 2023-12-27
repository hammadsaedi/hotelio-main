<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Hotel;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        if ($user->Role == 'Admin') {
            // Fetch hotels associated with the admin user
            $Hotels = Hotel::where('Admin', $user->id)->get();
            $Employees = Employee::whereIn('HotelID', $Hotels->pluck('id')->toArray())->get();
            $User = User::whereIn('EmployeeID', $Employees->pluck('id')->toArray())->get();
            if (request()->ajax()) {
                return Datatables::of($User)->addColumn('action','layouts.user_action')->make(true);
            }
            return view('user.index');
        }
        if (request()->ajax()) {
            return Datatables::of(User::all())->addColumn('action','layouts.user_action')->make(true);
        }
        return view('user.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = auth()->user();
        if($user->Role == 'Admin'){
            $Hotels = Hotel::where('Admin', $user->id)->get();
            $Employees = Employee::whereIn('HotelID', $Hotels->pluck('id')->toArray())->get();
            return view('user.create', compact('Employees'));
        }
        $Employees = Employee::all();
        return view('user.create', compact('Employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = new User();
        $user->EmployeeID = $request->EmployeeID;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();
        return back();
    }

    public function assignRole(Request $request)
    {
        // return $request->all();
        return User::find($request->UserID)->update(['Role' => $request->Role]);
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
    public function edit($id)
    {
        return view('user.edit');
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        User::find($id)->delete();
        return back();
    }
   /**
     * Delete all table list
    */
    public function destroyAll()
    {
        User::withTrashed()->delete();
        return back();
    }
}
