<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ReportController;
use App\Models\Auth\User;
use App\Models\Auth\AccountStatus;
use App\Models\Auth\UserType;

use Carbon\Carbon;

class DemoCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        \Log::info("Cron is working fine!");
        $users = User::get();
        // User::whereNotNull('chekrreportid')->where('chekrreportid', '!=', '')
                    // ->whereNotNull('chekrstatus ')
                    // ->whereNotNull('sex_offender_status')
                    // ->whereNotNull('national_status')
                    // ->whereNotNull('national_status')
                    // ->get();
                    // if($users){
                    //     \Log::info("Total Users to check for report ". count($users));
                    // }
                    // else{
                    //     \Log::info("No Users ");
                    // }
                    
        foreach($users as $user){
            $this->checkReport($user);
        }

    $this->CheckForBDays();
        // return 0;
    }
    
    private function CheckForBDays(){
        \Log::info("Cron BDay is working fine!");
        $users = User::get();
                    
        foreach($users as $user){
            $bday = $user->dob;
            if($bday === NULL || $bday === ""){
                \Log::info("Bday is not added");
            }
            else{
                \Log::info("Bday is " . $bday);
                $date = Carbon::now()->addWeeks(2)->format('m/d');
                if(strpos($bday, $date) === 0){
                    //send email
                    \Log::info("Email should be sent for BDay " . $bday . " 2 weeks after " . $date);
                    $data = array('user_name'=> $user->name, "user_email" => "info@braverhospitality.com", "user_message" => "");
            // $data = array('user_name'=> "Hammad", "user_email" => "admin@braverhospitality.com", "user_message" => "");
                Mail::send('Mail/bdayemail', $data, function ($message) use ($data, $user) {
                    //send to $user->email
                        $message->to("salmanmajid14@gmail.com",'Birthday')->subject('Happy Birthday');
                        $message->from($data['user_email']);
                    });
                }
            }
        }
    }


    private function checkReport($user){
        $userid = $user->userid;
        // $user = User::where('userid', $userid)->first();

        $rep = new ReportController();
        if($user->chekrreportid != NULL){
            // return "report id not null";
            if($user->ssn_trace == 'complete' && $user->national_status == 'complete' && $user->sex_offender_status == 'clear' && $user->chekrstatus == 'clear'){
                // get no need to get report
                // User::where('userid', $userid)->update(['accountstatus'=> 'Approved']);

                $user->accountstatus = AccountStatus::Approved;
                $user->save();
                \Log::info("Cron: User already created report and status is clear". $user->userid);
                // return $user;
            }
            else{
                \Log::info("Cron: User already created report and status is not clear". $user->userid);
                $cont = new Controller();
                $report = $cont->getchekrreportFromServer($user);
                // return response()->json(['status' => "1",
                //         'message'=> 'Report created',
                //         'data' => $report, 
                // ]);
            }
            // return "getting report details";
            
        }
        else{
            $id = $this->createCandidate($user);
            $report = $rep->getCheckrReport($id);
            $report_error = null;
            // return $report;
            if(!isset($report->error)){
                \Log::info("Cron: User created newly " . $user->userid);
                $id = $report->id;
                User::where('userid', $user->userid)->update(['chekrreportid' => $id]);
                $user = User::where('userid', $user->userid)->first();
                // return response()->json(['status' => "1",
                //     'message'=> 'Report created',
                //     'data' => new UserProfileFullResource($user), 
                // ]);
            }
            else{
                $report_error = $report->error . " " . $user->userid;
                \Log::info("Cron: ". $report_error);
                // return response()->json(['status' => "0",
                //     'message'=> $report_error,
                //     'data' => null, 
                // ]);
            }
        }
    }


    public function createCheckrCandidate($data){
        $data["copy_requested"] = true;
        $data['no_middle_name'] = true;
        $api_key = env('chekrapikey');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.checkr.com/v1/candidates');
        curl_setopt($curl, CURLOPT_USERPWD, $api_key . ":");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        
        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);
        
        $json = json_decode($response, true);
        return $json;
        if(array_key_exists('id', $json)){
            return $json['id'];
        }
        return NULL;
    }


    private function createCandidate($user){
            if($user->chekrcandidateid == NULL){
                            $dob = '';
                            
                            if($user->dob){
                                // return ["date" => "". $user->dob];
                                $dob = Carbon::createFromFormat('m/d/Y', $user->dob)->format('Y-m-d');
                                
                                $data = [
                                    "first_name" => $user->name,
                                    "last_name" => $user->name,
                                    "phone" => $user->phone,
                                    "email" => $user->email,
                                    "dob" => $dob,
                                    "ssn" => $user->ssn,
                                    "zipcode"=>$user->zip,
                                ];
                                
                                $json = $this->createCheckrCandidate($data);
                                
                                if(array_key_exists('id', $json)){
                                    $user->chekrcandidateid = $json['id'];
                                    
                                    User::where('userid', $user->userid)->update(['chekrcandidateid' => $json["id"]]);
                                    return $json['id'];
    
                                }
                                else{
                                    $chekr_error = $json['error'];
                                    return NULL;
                                }
                            }
                            else{
                                
                            }
            }
            else{
                return $user->chekrcandidateid;
            }
        }
}
