<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Reminder;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Tymon\JWTAuth\Facades\JWTAuth;




use Illuminate\Support\Facades\Mail;
use App\Mail\Ttt;

use App\Jobs\ProcessSendNotification;
use Carbon\Carbon;

class ReminderController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwtauth');
    }

    public function ttt(Request $request)
    {
        function is_same_time($date)
        {
            $today =  Carbon::now(config('app.timezone'));
            $isSameDay = $today->isSameDay($date);
            $isSameHour = $today->isSameHour($date);
            $isSameMinute = $today->isSameMinute($date);
            return $isSameDay && $isSameHour && $isSameMinute;
        }

        $reminders = Reminder::where('send_notification', 1)
        ->join('users', 'users.id', '=', 'reminders.user_id')
        ->select(
            'reminders.id',
            'reminders.user_id',
            'users.name',
            'users.email',
            'reminders.title',
            'reminders.description',
            'reminders.send_time',
            'reminders.send_notification',
            )
        ->get();

        collect($reminders)->each(function ($item, $key) {

            $date = $item->send_time;

            if (is_same_time($date)) {
                $description = $item->description ? $item->description:'無備註';
                $details = [
                    'email' => $item->email,
                    'title' => $item->title,
                    'description' => $description,

                ];
                dump($details);
            }
        });

        return response()->json($reminders);

    }

    public function index(Request $request)
    {
        // JWTAuth::parseToken()->authenticate();
        // return response()->json(auth()->user());


        if ($request->get('is_done')) {

            $is_done = $request->get('is_done') == 'true' ? true : false;

            return Reminder::where('is_done', $is_done)
            ->where('user_id',auth()->user()->id)
            ->orderBy('id', 'DESC')->get();
        }
        return Reminder::where('user_id',auth()->user()->id)
        ->orderBy('id', 'DESC')->get();
    }
    public function store(Request $request)
    {

        try {

            $validateRuleArr = [
                'title' => ['required'],
            ];

            if ($request->input('send_notification')) {
                $validateRuleArr['send_time'] = ['required'];
            }

            $validator = Validator::make($request->all(), $validateRuleArr, [

                'required' => ':attribute不可空白',
            ], [

                'title' => '提醒事項主題',
                'description' => '提醒事項主題備註',
                'send_time' => '寄送時間',
            ]);



            if ($validator->fails()) {


                return response()->json(['errors' => $validator->errors()]);
            };


            $reminder = new Reminder();
            $fillable = collect($reminder->getFillable())->toArray();



            $formField = $request->only($fillable);

            $formField['user_id']=auth()->user()->id;

            $reminder->create($formField);
            return response()->json(['error' => 'null',
            'data' => $reminder,
            'user_id'=> auth()->user()->id,
        ]);
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

        $details = ['email' => 'twkhjl@gmail.com'];

        ProcessSendNotification::dispatch($details);
        return response()->json('email sent');
    }
    public function toggle_is_done(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [

                'is_done' => ['required'],

            ], [

                'required' => ':attribute不可空白',
            ], [
                'is_done' => '完成狀態',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()]);
            };


            $reminder = Reminder::find($request->input('id'));
            $fillable = ['is_done'];
            $formField = $request->only($fillable);
            $reminder->update($formField);
            return response()->json(['error' => 'null', 'data' => $reminder]);
        } catch (\Exception $e) {

            return
                response()->json(['error' => 'server', 'message' => $e->getMessage()]);
        }
    }
}
