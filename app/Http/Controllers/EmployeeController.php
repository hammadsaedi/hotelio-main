<?php

namespace App\Http\Controllers;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yajra\Datatables\Datatables;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Hotel;
use Exception;


class EmployeeController extends Controller
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
        
            // Extract hotel IDs from the fetched hotels
            $hotelIds = $Hotels->pluck('id')->toArray();
        
            // Fetch employees associated with the extracted hotel IDs
            $Employees = Employee::whereIn('HotelId', $hotelIds)->get();
        
            if (request()->ajax()) {
                return Datatables::of($Employees)
    
                    ->addColumn('Hotel', function($employee) {
    
                        $hotelName = Hotel::where('id', $employee->HotelID)->value('Name');
                        return $hotelName;
                    })
    
                    ->addColumn('action', 'layouts.dt_buttons')
    
                    ->make(true);
            }
        
            return view('employee.index', compact('Hotels', 'Employees'));
        }

        $Employees = Employee::all();
        $Hotels = Hotel::all();

        if (request()->ajax()) {
            return Datatables::of($Employees)

                ->addColumn('Hotel', function($employee) {

                    $hotelName = Hotel::where('id', $employee->HotelID)->value('Name');
                    return $hotelName;
                })

                ->addColumn('action', 'layouts.dt_buttons')

                ->make(true);
        }

        return view('employee.index', compact('Hotels', 'Employees'));
    }

    public function dtQuery()
    {
        return $Employees = Employee::select('employees.*', 'hotels.Name as Hotel')
        ->leftJoin('hotels', 'employees.HotelID', '=', 'hotels.id')
        ->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $Hotels = Hotel::all();
        return view('employee.create', compact('Hotels'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $request->all();
        try {
            Employee::create($request->all());
            return 'Employee Add Successfull!' ;
        } catch (Exception $error) {
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
        $Employee = Employee::select('employees.*', 'hotels.Name as HotelName')
            ->where('employees.id', $id)
            ->leftJoin('hotels', 'employees.HotelID', '=', 'hotels.id')
            ->first();
        return $Employee;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // return Employee::all();
        $Hotels = Hotel::all();
        $Employees = Employee::find($id);
        return view('employee.edit', compact('Hotels', 'Employees'));
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
        Employee::find($id)->update($request->all());
        return "Data Update Successfully !";
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Employee::find($id)->delete();
        return back()->with('delete','Deleted data is stored in the trash');
    }

    /**
     * Destroy All Data on table 
     * 
     * @return \Illuminate\Http\Response
     */
    public function destroyAll()
    {
        Employee::withTrashed()->delete();
        return back()->with('destroyAll','Deleted All data is stored in the trash');
    }


    public function trash()
    {
        $EmployeesTrashed = Employee::onlyTrashed()->get();
        return view('employee.trash', compact('EmployeesTrashed'));
    }


    public function forceDeleted($id)
    {
        Employee::withTrashed()->where('id', $id)->forceDelete();

        return back()->with('Parmanentlly', 'Parmanentlly Delete');
    }


    public function restore($id)
    {
        Employee::withTrashed()->where('id', $id)->restore();

        return back()->with('restore', 'Restore Successfully!');
    }


    public function restoreAll()
    {
        Employee::withTrashed()->restore();
        return back()->with('restoreAll', ' ');
    }


    public function emptyTrash()
    {
        Employee::onlyTrashed()->forceDelete();
        return back()->with('emptyTrash', ' ');
    }
}
