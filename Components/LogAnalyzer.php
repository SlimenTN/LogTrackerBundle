<?php
namespace SBC\LogTrackerBundle\Components;
use SBC\LogTrackerBundle\Entity\Log;
use Symfony\Component\Finder\SplFileInfo;


/**
 * Class LogAnalyzer
 * @package SBC\LogTrackerBundle\Components
 * Parse log file into Log object
 * Arnaout slimen <arnaout.slimen@sbc.tn>
 */
class LogAnalyzer
{

    /**
     * @var SplFileInfo
     */
    private $logFile;
    
    public function __construct(SplFileInfo $logFile)
    {
        $this->logFile = $logFile;
    }

    /**
     * Parse file and return objects
     * @return array
     */
    public function parse(){
        $logs = array();
        $rows = explode("\n", $this->logFile->getContents());
        foreach($rows as $line)
        {
            $log = $this->parseLine($line);
            if($log != null) $logs[] = $log;
        }

        $logs = $this->sort($logs);

        return $logs;
    }

    /**
     * Sort array by dat of log
     * @param array $logs
     * @return array
     */
    private function sort(array $logs){
        usort($logs, array($this, 'compare'));
        return $logs;
    }

    /**
     * Compare date log
     * @param Log $a
     * @param Log $b
     * @return int
     */
    private function compare(Log $a, Log $b){
        return ($a->getDateLog() > $b->getDateLog()) ? -1 : 1;
    }

    /**
     * Parse log line and return a Log entity
     * @param string $line
     * @return Log
     */
    private function parseLine($line){
        $log = null;
        $time = $this->parseTime($line);
        if($time != null){
            $log = new Log();
            $log->setDateLog($time);

            $logDetails = substr($line, 22, strlen($line));
            list($sns, $description) = explode(':', $logDetails, 2);
            list($source, $status) = explode('.', $sns);
            $log->setStatus($status);
            $log->setLogSource($source);
            $log->setDescription(trim($description));

        }


        return $log;
    }

    /**
     * Get log time
     * @param $line
     * @return \DateTime
     */
    private function parseTime($line){
        $time = null;
        $timePattern = "/\[(.*?)]/";//----get any string that starts with "[" and ends with "]"
        preg_match($timePattern, $line, $time);
        return (empty($time)) ? null : \DateTime::createFromFormat('Y-m-d H:i:s', $time[1]);
    }
    
}