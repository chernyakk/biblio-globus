<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\{Alignment, Border};
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelFile
{
    private $data;
    private $spreadSheet;

    public function __construct($array, $userId)
    {
        $this->data = $this->formatArray($array);
        $this->spreadSheet = new Spreadsheet();
    }

    public function setValues() {
        $nowArray = $this->data;
        $this->spreadSheet->getActiveSheet()->fromArray($nowArray, NULL, 'A1');
        foreach(range('A','F') as $columnID){
            $this->spreadSheet->getActiveSheet()->getColumnDimension($columnID) ->setAutoSize(true);
            $this->spreadSheet->getActiveSheet()->getStyle($columnID . "1")->applyFromArray(
                [
                    'font' => [
                        'bold' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]
            );
        }

        $file = new Xlsx($this->spreadSheet);
        $nowDate = new \DateTime();
        try {
            $file->save('./storage/hotels' . $nowDate->format('dmYHis') . '.xlsx');
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return Storage::url('hotels' . $nowDate->format('dmYHis') . '.xlsx');
    }

    private function formatArray(array $array){
        $returnArray = [];
        $percent = $array['percent'];
        $array = $array['hotels'];
        $hotelNames = DB::table('user_values')
            ->select('name', 'api_id')
            ->where('entity', '=', 'hotel')
            ->get();
        for ($i = 0; $i < count($array); $i++){
            $element = $array[$i];
            array_push($returnArray, [
                '№' => $i + 1,
                'Отель' => $hotelNames
                    ->where('api_id', '=', $element['id_hotel'])->first()->name,
                'Номер' => $element['room'],
                'Количество ночей' => $element['duration'],
                'Цена' => $element['prices'][0]['amount'],
                'Цена с наценкой в ' . $percent . '%' => round($element['prices'][0]['amount'] * (float)('1.' . $percent)),
            ]);
        }
        array_unshift($returnArray, array_keys($returnArray[0]));
        return $returnArray;
    }
}
