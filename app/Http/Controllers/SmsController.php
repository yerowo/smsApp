<?php

namespace App\Http\Controllers;

use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function index()
    {
        return view('send');
    }

    public function sendSms(Request $request, SmsService $smsService)
    {
        // Validate the form input
        $request->validate([
            'sender_id' => 'required|string|min:3|max:11',
            'recipients' => 'required|string',
            'message' => 'required|string',
        ]);

        $senderId = $request->input('sender_id');
        $recipients = $request->input('recipients');
        $message = $request->input('message');

        $processRecipients = $smsService->processRecipientNumbers($recipients);

        // Calculate unit charge per page for each recipient
        $unitChargeRetrunValue = $smsService->calculateUnitCharge($processRecipients);

        // if you want prifix and price this will get each recipient submitted eg(234805-2.2, 234703 1.9)
        $unitCharge = $unitChargeRetrunValue[0];

        // to calculate total
        $addAllUnitCharges = $unitChargeRetrunValue[1];

        // number(s) attempting to send the message to
        $unitCount = $unitChargeRetrunValue[2];


        // Count the message text per letter
        $messageLength = strlen($message);
        $charactersPerPage = 160;
        $firstPageCharacters = 160;
        $otherPagesCharacters = 154;

        $pages = ceil($messageLength / $charactersPerPage);
        $currentPage = ceil($messageLength / $charactersPerPage);
        $charactersLeft = 0;

        if ($currentPage === 1) {
            $charactersLeft = $firstPageCharacters - ($messageLength % $charactersPerPage);
        } else {
            $charactersLeft = $otherPagesCharacters - ($messageLength % $charactersPerPage);
        }

        $totalCharge = number_format(($pages * $addAllUnitCharges), 2);

        // Prepare the summary/breakdown
        $summary = [
            'processRecipients' => $processRecipients,
            'pages' => $pages,
            'addAllUnitCharges' => $addAllUnitCharges,
            'unitCount' => $unitCount,
            'totalCharge' => $totalCharge,
        ];

        // Return the summary as a JSON response
        return response()->json($summary);
    }
}
