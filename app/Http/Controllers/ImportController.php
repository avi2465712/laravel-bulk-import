<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\ImportContactsJob;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\ThankYouContactMail;

class ImportController extends Controller
{


    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index()
    {
        return view('import');
    }

    /* ================= IMPORT ================= */

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $file = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($file);

        $chunkSize = 1000;
        $batch = [];
        $count = 0;

        while (($row = fgetcsv($file)) !== false) {
            $batch[] = array_combine($header, $row);
            $count++;

            if ($count % $chunkSize === 0) {
                ImportContactsJob::dispatch($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            ImportContactsJob::dispatch($batch);
        }

        fclose($file);

        return redirect()->back()->with('success', 'Import started successfully');
    }

    /* ================= DATATABLE ================= */

    public function contactsData()
    {
        return datatables()->of(
            DB::table('contacts')->select('id','fname','lname','email','mobile')
        )
        ->addColumn('action', function ($row) {
            return '
                <button class="btn btn-sm btn-info editBtn" data-id="'.$row->id.'">Edit</button>
                <button class="btn btn-sm btn-danger deleteBtn" data-id="'.$row->id.'">Delete</button>
            ';
        })
        ->rawColumns(['action'])
        ->make(true);
    }

    /* ================= CRUD ================= */

    // public function store(Request $request)
    // {
    //     DB::table('contacts')->insert([
    //         'fname' => $request->fname,
    //         'lname' => $request->lname,
    //         'email' => $request->email,
    //         'mobile' => $request->mobile,
    //         'created_at' => now(),
    //         'updated_at' => now()
    //     ]);

    //     return response()->json(['success' => true]);
    // }


    // public function store(Request $request)
    // {
    //     // ✅ Validation
    //     $request->validate([
    //         'fname'  => 'required|string|max:255',
    //         'mobile' => 'required|digits:10|unique:contacts,mobile',
    //         'email'  => 'nullable|email',
    //         'lname'  => 'nullable|string|max:255',
    //     ], [
    //         'mobile.unique' => 'This mobile number already exists.'
    //     ]);

    //     DB::table('contacts')->insert([
    //         'fname' => $request->fname,
    //         'lname' => $request->lname,
    //         'email' => $request->email,
    //         'mobile' => $request->mobile,
    //         'created_at' => now(),
    //         'updated_at' => now()
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Contact added successfully'
    //     ]);
    // }


    public function store(Request $request)
    {
        $request->validate([
            'fname'  => 'required|string|max:255',
            'mobile' => 'required|digits:10|unique:contacts,mobile',
            'email'  => 'required|email',
            'lname'  => 'nullable|string|max:255',
        ], [
            'mobile.unique' => 'This mobile number already exists.'
        ]);

        $contactId = DB::table('contacts')->insertGetId([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Get saved contact
        $contact = DB::table('contacts')->where('id', $contactId)->first();

        // ✅ Send Thank You Email
        Mail::to($contact->email)->send(new ThankYouContactMail($contact));

        return response()->json([
            'success' => true,
            'message' => 'Contact added & Thank You email sent'
        ]);
    }

    public function edit($id)
    {
        return DB::table('contacts')->where('id', $id)->first();
    }

    // public function update(Request $request, $id)
    // {
    //     DB::table('contacts')->where('id', $id)->update([
    //         'fname' => $request->fname,
    //         'lname' => $request->lname,
    //         'email' => $request->email,
    //         'mobile' => $request->mobile,
    //         'updated_at' => now()
    //     ]);

    //     return response()->json(['success' => true]);
    // }

    public function update(Request $request, $id)
    {
        $request->validate([
            'fname'  => 'required|string|max:255',
            'mobile' => 'required|digits:10|unique:contacts,mobile,' . $id,
            'email'  => 'nullable|email',
            'lname'  => 'nullable|string|max:255',
        ], [
            'mobile.unique' => 'This mobile number already exists.'
        ]);

        DB::table('contacts')->where('id', $id)->update([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contact updated successfully'
        ]);
    }


    public function destroy($id)
    {
        DB::table('contacts')->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }
}
