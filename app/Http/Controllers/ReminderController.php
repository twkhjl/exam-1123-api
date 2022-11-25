<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;



use Illuminate\Support\Facades\Mail;
use App\Mail\Ttt;




class ReminderController extends Controller
{
    //

    public function index()
    {

        // return Reminder::all();
        return Reminder::orderBy('id', 'DESC')->get();
    }
    public function store(Request $request)
    {
        // return response()->json($request);

        try {

            if ($request->input('img') == null) {
                $request->request->remove('img');
            }
            $validator = Validator::make($request->all(), [

                'title' => ['required'],
                // 'description' => ['required'],

            ], [

                'required' => ':attribute不可空白',
            ], [

                'title' => '提醒事項主題',
                'description' => '提醒事項主題備註',
            ]);



            if ($validator->fails()) {


                return response()->json(['errors' => $validator->errors()]);
            };



            $reminder = new Reminder();
            $fillable = collect($reminder->getFillable())->toArray();
            $formField = $request->only($fillable);


            $reminder->create($formField);
            return response()->json(['error' => 'null', 'data' => $reminder]);
        } catch (\Exception $e) {

            return
                response()->json(['error' => 'server', 'message' => $e->getMessage()]);
        }
    }
    public function destroy(Request $request)
    {
        try {
            $reminder = Reminder::find($request->input('id'));
            if (!$reminder) {
                return response()->json(['error' => 'server', 'message' => 'record not found']);
            }
            $title = $reminder->title;

            $reminder->delete();

            return response()->json(['error' => null, 'message' => 'record deleted', 'title' => $title]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'server', 'message' => $e]);
        }
    }
    public function update(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [

                'title' => ['required'],

            ], [

                'required' => ':attribute不可空白',
            ], [
                'title' => '提醒事項主題',
                'description' => '提醒事項主題備註',
            ]);



            if ($validator->fails()) {


                return response()->json(['errors' => $validator->errors()]);
            };



            $reminder = Reminder::find($request->input('id'));
            $fillable = collect($reminder->getFillable())->toArray();
            $formField = $request->only($fillable);


            $reminder->update($formField);
            return response()->json(['error' => 'null', 'data' => $reminder]);
        } catch (\Exception $e) {

            return
                response()->json(['error' => 'server', 'message' => $e->getMessage()]);
        }
    }
    public function send(Request $request)
    {

        Mail::to('someone@kkkkkk.com')->send(new Ttt());
        return response()->json('email sent');
    }
}
