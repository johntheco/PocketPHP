<?php


final class Debug
{
    public static function queryDebugInfo()
    {
        $debugInformation = [
            (Pocket::requestExecutionTime()) ?: "",
            (Pocket::queryExecutionTime()) ?: "",
            (Pocket::queryFetchTime()) ?: "",
            (Pocket::dbConnectTime()) ?: "",
            (Pocket::modelProcessingTime()) ?: "",
            (Pocket::routeProcessingTime()) ?: "",
            (Pocket::viewProcessingTime()) ?: "",
            (Pocket::viewHeaderProcessingTime()) ?: "",
            (Pocket::viewFooterProcessingTime()) ?: "",
            (Pocket::queryStatus()) ?: "",
            (Pocket::peakMemoryUsage()) ?: "",
        ];
    
        echo implode("<br>", $debugInformation);
    }

    public static function shutdownHandler($showPhpInfo = false)
    {
        echo "<span style='color: red'>Произошла критическая ошибка. Подробности:</span><pre>";
        print_r(error_get_last());
        
        if ($showPhpInfo) {
            echo "<br><br>";
            die(phpinfo());
        }
    }
}
