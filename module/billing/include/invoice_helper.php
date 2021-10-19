<?php

class InvoiceHelper
{
    public static function GetInvoiceCreationCompanyUnitConditionList($date)
    {
        $timestamp = strtotime($date);

        $dayOfYear = date("z", $timestamp);
        $dayOfMonth = date("j", $timestamp);
        $month = date("n", $timestamp);

        $conditionList = array();

        //yearly

        $conditionList[] = array(
            "payment_type" => "yearly",
            "invoice_date" => $dayOfYear
        );

        //quarterly
        if (in_array($month, array(1, 4, 7, 10))) {
            $conditionList[] = array(
                "payment_type" => "quarterly",
                "invoice_date" => $dayOfMonth
            );
        }
        if (in_array($month, array(2, 5, 8, 11))) {
            $daysBefore = date("t", strtotime($month - 1));
            $conditionList[] = array(
                "payment_type" => "quarterly",
                "invoice_date" => $dayOfMonth + $daysBefore
            );
        }
        if (in_array($month, array(3, 6, 9, 12))) {
            $daysBefore = date("t", strtotime($month - 1)) + date("t", strtotime($month - 2));
            $conditionList[] = array(
                "payment_type" => "quarterly",
                "invoice_date" => $dayOfMonth + $daysBefore
            );
        }

        //monthly
        $conditionList[] = array(
            "payment_type" => "monthly",
            "invoice_date" => $dayOfMonth
        );

        return $conditionList;
    }

    public static function GetInvoicePeriodAfter($date, $paymentType)
    {
        /*
                $timestamp = strtotime($date);

                $dayOfMonth = date("j", $timestamp);
                $month = date("n", $timestamp);
                $lastDayOfMonth = date("t", $timestamp);

                $dateFrom = new DateTime($date);
                $dateTo = new DateTime($date);

                if($paymentType == "yearly")
                {
                    $dateTo->modify("+1 year");
                    $dateTo->modify("-1 day");
                }
                elseif($paymentType == "quarterly")
                {
                    $dateTo->modify("+3 month");
                    $dateTo->modify("-1 day");
                }
                elseif($paymentType == "monthly")
                {
                    $dateFrom->modify("-".($dayOfMonth-1)." days");
                    $dateTo->modify("+".($lastDayOfMonth-$dayOfMonth)." days");
                }

                $from = $dateFrom->format("Y-m-d");
                $to = $dateTo->format("Y-m-d");

                if($from == $date && $to == $date)
                {
                    //dates were not modified - no conditions are met
                    return null;
                }
        */

        $dateFrom = date("Y-m-01", strtotime($date));
        $dateTo = date("Y-m-15", strtotime($date));

        if ($paymentType == "yearly") {
            $dateTo = date("Y-12-31", strtotime($dateTo));
        } elseif ($paymentType == "quarterly") {
            $dateTo = date_create($dateTo)->modify('+2 month')->modify('last day of this month')->format("Y-m-d");
        } elseif ($paymentType == "monthly") {
            $dateTo = date_create($dateTo)->modify('last day of this month')->format("Y-m-d");
        }

        return array($dateFrom, $dateTo);
    }

    public static function GetInvoicePeriodBefore($date, $paymentType)
    {
        /*
                $timestamp = strtotime($date);

                $dayOfMonth = date("j", $timestamp);
                $month = date("n", $timestamp);
                $lastDayOfMonth = date("t", $timestamp);

                $dateFrom = new DateTime($date);
                $dateTo = new DateTime($date);

                if($paymentType == "yearly")
                {
                    $dateFrom->modify("-1 year");
                    $dateTo->modify("-1 day");
                }
                elseif($paymentType == "quarterly")
                {
                    $dateFrom->modify("-3 month");
                    $dateTo->modify("-1 day");
                }
                elseif($paymentType == "monthly")
                {
                    $dateFrom->modify("-1 month -".($dayOfMonth-1)." days");
                    $dateTo->modify("-".$dayOfMonth." days");
                }

                $from = $dateFrom->format("Y-m-d");
                $to = $dateTo->format("Y-m-d");

                if($from == $date && $to == $date)
                {
                    //dates were not modified - no conditions are met
                    return null;
                }
        */
        $dateFrom = date("Y-m-15", strtotime($date));
        $dateTo = date_create($date)->modify('last day of previous month')->format("Y-m-d");

        if ($paymentType == "yearly") {
            $dateFrom = date_create($dateFrom)->modify('-1 year')->modify('first day of this month')->format("Y-m-d");
        } elseif ($paymentType == "quarterly") {
            $dateFrom = date_create($dateFrom)->modify('-3 month')->modify('first day of this month')->format("Y-m-d");
        } elseif ($paymentType == "monthly") {
            $dateFrom = date_create($dateFrom)->modify('first day of previous month')->format("Y-m-d");
        }

        return array($dateFrom, $dateTo);
    }
}
