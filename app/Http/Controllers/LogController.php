<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Logs;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    private $currentDatetime;
    private $sessionUser;
    private $roles;
    private $rights;
    private $assignedSites;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->currentDatetime = Carbon::now('Asia/Karachi')->timestamp;
            $this->sessionUser = session('user');
            $this->roles = session('role');
            $this->rights = session('rights');
            $this->assignedSites = session('sites');
            // if (Auth::check() && Auth::user()->role_id == 1) {
            if (Auth::check()) {
                return $next($request);
            } else {
                return redirect('/');
            }
        });
    }

    public function ViewLogs($id)
    {
        $logIds = explode(',', $id);
        $logs = DB::table('logs')
                ->whereIn('id', $logIds)
                ->orderByDesc('id')
                ->get();


        if ($logs->isNotEmpty()) {
            $data = [];
            foreach ($logs as $log) {
                $module = $log->module;
                $content = ucwords($log->content);
                $event = $log->event;

                $timestamp = $log->timestamp;
                $timestamp = Carbon::createFromTimestamp($timestamp);
                $timestamp = $timestamp->format('l d F Y - h:i A');

                $data[] = [
                    'module' => $module,
                    'content' => $content,
                    'event' => $event,
                    'timestamp' => $timestamp,
                ];
            }

            return response()->json($data);
        }
        else {
            return response()->json(['error' => 'Logs not found'], 404);
        }
    }
}
