<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\SoftDeletes;
use Yajra\Datatables\Datatables;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Hotel;
use App\Models\Employee;
use Exception;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        if ($user->Role == 'SuperAdmin') {
            $Rooms = Room::all();
            $Hotels = Hotel::all();
            if (request()->ajax()) {
                return Datatables::of($Rooms)
                    ->addColumn('HotelName', function($room) {

                        $hotelName = Hotel::where('id', $room->HotelID)->value('Name');
                        return $hotelName;
                    })
                    ->addColumn('action', 'layouts.dt_buttons')
                    ->make(true);
            }
            return view('room.index', compact('Hotels', 'Rooms'));
        } else if ($user->Role == 'Admin') {
            // Fetch hotels associated with the admin user
            $Hotels = Hotel::where('Admin', $user->id)->get();
        
            // Extract hotel IDs from the fetched hotels
            $hotelIds = $Hotels->pluck('id')->toArray();
        
            // Fetch rooms associated with the extracted hotel IDs
            $Rooms = Room::whereIn('HotelId', $hotelIds)->get();
        
            if (request()->ajax()) {
                return Datatables::of($Rooms)

                    ->addColumn('HotelName', function($room) {

                        $hotelName = Hotel::where('id', $room->HotelID)->value('Name');
                        return $hotelName;
                    })

                    ->addColumn('action', 'layouts.dt_buttons')

                    ->make(true);
            }
        
            return view('room.index', compact('Hotels', 'Rooms'));
        }
        
        $employeeId = $user->EmployeeID;

        $employee = Employee::find($employeeId);

        if ($employee) {
            $hotelId = $employee->HotelID;
            $employee = Employee::find($employeeId);
            $employeeHotelId = $employee->HotelID;
            $Hotels = Hotel::where('id', $employeeHotelId)->get();

            // Fetch rooms associated with the hotel ID directly
            $Rooms = Room::where('HotelID', $hotelId)->get();

            if (request()->ajax()) {
                return Datatables::of($Rooms)

                    ->addColumn('HotelName', function($room) {

                        $hotelName = Hotel::where('id', $room->HotelID)->value('Name');
                        return $hotelName;
                    })

                    ->addColumn('action', 'layouts.dt_buttons')

                    ->make(true);
            }

            return view('room.index', compact('Hotels', 'Rooms'));
        }

        return redirect()->route('home')->with('error', 'Employee details not found.');
    }

    public function dtQuery()
    {
        return $Rooms = Room::select('rooms.*','hotels.Name as HotelName')
        ->leftJoin('hotels','rooms.HotelID','=','hotels.id')
        ->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $Hotels= Hotel::all();
        return view('room.create',compact('Hotels'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
        try{
            Room::create($request->all());
            return 'Room Added SuccessFully !';
            // return back()->with('Success','Room Added SuccessFully !');
        }
        catch(Exception $error){
            return $error->getMessage();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $Room = Room::find($id);
        $Room = Room::select('rooms.*','hotels.Name as HotelName')
        ->where('rooms.id',$id)
        ->leftJoin('hotels','rooms.HotelID','=','hotels.id')
        ->first();
        return $Room;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $Hotels= Hotel::all();
        $Room = Room::find($id);
        return view('room.edit' , compact('Room','Hotels'));
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
        
        Room::find($id)->update($request->all());
        return back()->with('Success','Hotel Update SuccessFully !');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Room::find($id)->delete();
        return back()->with('Destroy','Delete Completed !');
    }

    /**
     * Delete all table list
    */
    public function destroyAll()
    {
        Room::withTrashed()->delete();
        return back()->with('DestroyAll', 'All data needs to be cleared');
    }
    /**
     * View Trash page 
    */
    public function trash()
    {
        $Rooms = Room::onlyTrashed()->get();
        // $Rooms = Room::onlyTrashed()->get();
        return view('room.trash',compact('Rooms'));
    }
    /**
     * table column restore
    */
    public function restore($id)
    {
        Room::withTrashed()->where('id',$id)->restore();
        return back()->with('Restore','Restore SuccessFully !');
    }
    /**
     * Table  all Column list restore
    */
    public function restoreAll()
    {
        Room::withTrashed()->restore();
        return back()->with('RestoreAll','All data has been recovered');
    }
    /**
     * table remove delete
    */
    public function forceDeleted($id)
    {
        Room::withTrashed()->where('id',$id)->forceDelete();
        return back()->with('PermanentlyDelete', 'Permanently Delete Completed !');
    }
    /**
     * All table list remove
    */
    public function emptyTrash()
    {

        Room::onlyTrashed()->forceDelete();
        return back()->with('EmptyTrash', 'The trash is completely emptied');
    }
}
