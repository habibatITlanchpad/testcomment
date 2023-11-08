<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{
    protected $repository;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    public function index(Request $request)
    {
        $user = $request->input('user_id');

        if ($user) {
            $response = $this->repository->getUsersJobs($user);
        } elseif (
            $request->__authenticatedUser->user_type == config('constants.ADMIN_ROLE_ID') ||
            $request->__authenticatedUser->user_type == config('constants.SUPERADMIN_ROLE_ID')
        ) {
            $response = $this->repository->getAll($request);
        } else {
            $response = null; // Handle unauthorized access
        }

        return response()->json($response);
    }

    public function show($id)
    {
        $job = $this->repository->findWithRelations($id);

        return response()->json($job);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->storeJob($user, $data);

        return response()->json($response);
    }

    public function update($id, Request $request)
    {
        $data = $request->all();
        $cuser = $request->__authenticatedUser;

        $response = $this->repository->updateJob($id, $data, $cuser);

        return response()->json($response);
    }

    // Other methods...

    public function distanceFeed(Request $request)
    {
        $data = $request->all();

        $distance = $data['distance'] ?? "";
        $time = $data['time'] ?? "";
        $jobid = $data['jobid'] ?? "";
        $session = $data['session_time'] ?? "";
        $flagged = $data['flagged'] === 'true' ? 'yes' : 'no';
        $manually_handled = $data['manually_handled'] === 'true' ? 'yes' : 'no';
        $by_admin = $data['by_admin'] === 'true' ? 'yes' : 'no';
        $admincomment = $data['admincomment'] ?? "";

        if ($time || $distance) {
            Distance::where('job_id', $jobid)->update(['distance' => $distance, 'time' => $time]);
        }

        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            Job::where('id', $jobid)->update([
                'admin_comments' => $admincomment,
                'flagged' => $flagged,
                'session_time' => $session,
                'manually_handled' => $manually_handled,
                'by_admin' => $by_admin
            ]);
        }

        return response('Record updated!');
    }

    // Other methods...
}

