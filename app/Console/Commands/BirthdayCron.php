<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ReportController;
use App\Models\Auth\User;
use App\Models\User\BDayWish;
use App\Models\Auth\AccountStatus;
use App\Models\Auth\UserType;

use Carbon\Carbon;

class BirthdayCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'birthdayemail:cron';

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
        \Log::info("Cron BDay is working fine!");
        $users = User::get();
                    
        foreach($users as $user){
            $bday = $user->dob;
            if($bday === NULL || $bday === ""){
                \Log::info("Bday is not added");
            }
            else{
                \Log::info("Bday is " . $bday);
                $date = Carbon::now()->format('m/d/Y');
                if($date === $bday){
                    //send email
                    \Log::info("Email should be sent for BDay");
                }
            }
        }
    }
}
