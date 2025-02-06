<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Option;
use App\Models\Response;

class ProcessExamResponse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process-responses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        
        // Retrieve all options from the database
        $options = Option::all();


        $count=0;

       
        foreach($options as $option){
            echo "Search for matches for $option->id \n";

            $responses = Response::where('selected_option', $option->id);

            if ($responses->count() > 0) { // Check if there are any responses
                echo "Found " . $responses->count() . " matches for $option->id \n";
                echo "Proceeding to update the collection. \n";

                sleep(1);

                $responses->update([
                    'selected_option_text' => $option->option_text
                ]);

                echo "Update complete.\n";
                sleep(1);
            } else {
                echo "No matches found \n";
            }
        }

        echo "Process completed. \n";

        $countUpdatedRecords = Response::where('selected_option_text', '!=', null)->count();

        $countRemainingRecords = Response::whereNull('selected_option_text')->count();

        echo "Total records updated: $countUpdatedRecords \n";
        sleep(1);
        echo "Records left: $countRemainingRecords \n";





    }

    
}
