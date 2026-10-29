<?php
//set default value
$message = '';

//get value from POST array
$action = filter_input(INPUT_POST, 'action');
if ($action === NULL) {
    $action =  'start_app';
}

//process
switch ($action) {
    case 'start_app':
        try {
            // set default invoice date 1 month prior to current date
            $interval = new DateInterval('P1M');
            $default_date = new DateTime();
            $default_date->sub($interval);
            $invoice_date_s = $default_date->format('n/j/Y');

            // set default due date 2 months after current date
            $interval = new DateInterval('P2M');
            $default_date = new DateTime();
            $default_date->add($interval);
            $due_date_s = $default_date->format('n/j/Y');

            $message = 'Enter two dates and click on the Submit button.';
        }
        catch(Exception $e)
        {
            $message = $e->getMessage();
        }
        break;
    case 'process_data':
        //set correct timezone
        $timezone = new DateTimeZone('America/Chicago');

        //get data from the form
        $invoice_date_s = filter_input(INPUT_POST, 'invoice_date');
        $due_date_s = filter_input(INPUT_POST, 'due_date');

        //trim spaces
        $invoice_date_s = trim($invoice_date_s);
        $due_date_s = trim($due_date_s);

        //Pretty messy solution but this will ensure that no matter what is entered the
        //strings will not cause an error
        $invoice_date_s = str_replace(["-","."," "], "/", $invoice_date_s);
        $due_date_s = str_replace(["-","."," "], "/", $due_date_s);

        $invoice_date_s = preg_replace('/[^0-9\/]/', '', $invoice_date_s);
        $due_date_s = preg_replace('/[^0-9\/]/', '', $due_date_s);

        // make sure the user enters both dates
        if (empty($invoice_date_s) || empty($due_date_s))
        {
            $message .= "Please enter both dates <br>";
            break;
        }

        // and use a try/catch to make sure the dates are valid
        try
        {
            $current_date = new DateTime('now',$timezone);
            // convert date strings to DateTime objects
            try {
                $invoice_date_f = new DateTime($invoice_date_s);
            }
            catch(Exception $e)
            {
                $message = "Error: " . "Invalid invoice date <br>";

                $current_date_f = '';
                $current_time_f = '';
                $due_date_message = '';
                break;
            }
            try
            {
                $due_date_f = new DateTime($due_date_s);
            }
            catch(Exception $e)
            {
                $message = "Error: " . "Invalid due date <br>";

                $current_date_f = '';
                $current_time_f = '';
                $due_date_message = '';
                break;
            }


            //check for valid dates
            //check invoice date
            $year = $invoice_date_f->format('Y');
            $month = $invoice_date_f->format('m');
            $day = $invoice_date_f->format('d');
            if (!checkdate($month,$day,$year))
            {
                $message.= "The invoice date is invalid <br>";
            }

            //check due date
            $year = $due_date_f->format('Y');
            $month = $due_date_f->format('m');
            $day = $due_date_f->format('d');

            if (!checkdate($month,$day,$year))
            {
                $message.= "The due date is invalid <br>";
            }
        }
        catch (Exception $e)
        {
            $message = "Error: " . $e->getMessage();

            $current_date_f = '';
            $current_time_f = '';
            $due_date_message = '';
            break;
        }
        //halt the execution and display the message if the message strng isn't empty
        if ($message != "")
        {
            break;
        }

        // make sure the due date is after the current date
        if ($invoice_date_f >= $due_date_f)
        {
            $message.="The invoice date has to be before due date <br>";
            break;
        }


        //if the due date is today display the appropriate text, if it's in the future then display
        //due date in x years x months x days
        if ($current_date <= $due_date_f)
        {
            //get difference
            $due_date_span = $current_date->diff($due_date_f);
            $years = $due_date_span->y;
            $months = $due_date_span->m;
            $days = $due_date_span->d;
            if ($years == 0 && $months == 0 && $days == 0) {
                $due_date_message = "The invoice is due today";
            } else {
                $due_date_message = "The invoice is due in $years years, $months months, and $days days";
            }
        }
        //otherwise the invoice is overdue, display the appropriate message
        else
        {
            //get difference
            $due_date_span = $current_date->diff($due_date_f);
            $years = $due_date_span->y;
            $months = $due_date_span->m;
            $days = $due_date_span->d;
            $due_date_message = "The invoice is past due by $years years, $months months, and $days days";
        }

        // format invoice
        $invoice_date_f = $invoice_date_f->format('F d, Y');

        // get the current date and time and format them
        $current_date_f = $current_date->format('F d, Y');
        $current_time_f = $current_date->format('g:i:s a');

        // get the amount of time between the current date and the due date
        // and format the due date message
        $due_date_f = $due_date_f->format('F d, Y');

        break;
}
include 'date_tester.php';
