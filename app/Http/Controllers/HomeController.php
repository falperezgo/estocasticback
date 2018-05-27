<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class HomeController extends Controller {

    public function index() {

        $out = '';
        $out2 = '';
        $fila = 0;
        $a = [];
        $rows = [];
        $actors = [];
        if (($gestor = fopen(storage_path("app/public/out.txt"), "r")) !== FALSE) {
            while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) {
                $numero = count($datos);
                $fila++;
                if ($fila == 1) {
                    $lab = ['id' => 'round',
                        'label' => 'round',
                        'type' => 'string'
                    ];
                    array_push($actors, $lab);
                    for ($c = 0; $c < $numero; $c++) {
                        $lab = ['id' => $datos[$c],
                            'label' => $datos[$c],
                            'type' => 'number'
                        ];
                        array_push($actors, $lab);
                    }
                    array_push($a, ['cols' => $actors]);
                    continue;
                }

                $b = [];
                array_push($b, ['v' => $fila - 1]);
                for ($c = 0; $c < $numero; $c++) {
                    array_push($b, ['v' => floatval($datos[$c])]);
                }
                array_push($rows, ['c' => $b]);
            }
            array_push($a, ['rows' => $rows]);
            fclose($gestor);
        }
//        dd($a);
        return json_encode($a, JSON_PRETTY_PRINT);
    }

    public function run($actors) {
        $n = $actors - 1;
        $process = new Process('python3 ' . storage_path("app/public/bdm_scholz_model-jucjimenezmo.py") . ' ' . storage_path("app/public/input.csv") . ' ' . $n);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $out = $process->getOutput();
        $myfile = fopen(storage_path("app/public/out.txt"), "w") or die("Unable to open file!");
        fwrite($myfile, $out);
        fclose($myfile);

        //return $out;
//        $status = ['status'=>'ok'];
        return 'ok';
    }

    public function jsonToCsv(Request $request) {

        $json = $request->getContent();
//        dd($json);
        $decode = json_decode($json, true);

        ///dd($decode);

        $txt = "Actor,Capability,Position,Salience\n";

        $actors = 0;
        foreach ($decode as $actor) {
            $name = $actor['name'];
            $cap = $actor['capability'];
            $inf = $actor['influence'];
            $pos = $actor['position'];
            $txt.= $name . ',' . $cap . ',' . $pos . ',' . $inf . "\n";
            $actors++;
        }



        $myfile = fopen(storage_path("app/public/input.csv"), "w") or die("Unable to open file!");
        fwrite($myfile, $txt);
        fclose($myfile);

//        return $txt;

        return $this->run($actors);
    }

}
