<?php
namespace SBC\LogTrackerBundle\Controller;

use SBC\LogTrackerBundle\Components\LogAnalyzer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * Display log details of dev.log file
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function devLogAction(Request $request)
    {
        $logFile = $this->getLogFile('dev.log');

        if($request->query->has('clear')){//---id clear param exist the delete file content
            file_put_contents($logFile, "");
            return $this->redirectToRoute('log_tracker_dev');
        }

        $analyzer = new LogAnalyzer($logFile);
        $logs = $analyzer->parse();

        return $this->render('@LogTracker/dev.html.twig', array(
            'logs' => $logs,
        ));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function prodLogAction(Request $request){
        $logFile = $this->getLogFile('prod.log');
        $logs = array();

        if($request->query->has('clear')){//---id clear param exist the delete file content
            file_put_contents($logFile, "");
            return $this->redirectToRoute('log_tracker_prod');
        }

        if($logFile != null){
            $analyzer = new LogAnalyzer($logFile);
            $logs = $analyzer->parse();
        }

        return $this->render('@LogTracker/prod.html.twig', array(
            'logs' => $logs,
        ));
    }

    /**
     * @param $fileName
     * @return null
     */
    private function getLogFile($fileName){
        $fullPath = $this->get('kernel')->getRootDir();
        $path = $fullPath.'/../var/logs/';
        $finder = new Finder();
        $finder->files()->in($path);

        $logFile = null;

        foreach ($finder as $file) {
            if($file->getRelativePathname() == $fileName) {
                $logFile = $file;
                break;
            }
        }
        return $logFile;
    }

}
