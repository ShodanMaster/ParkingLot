<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements FromCollection, WithHeadings, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data)->map(function ($item) {
            return [
                $item['DT_RowIndex'],
                $item['qrcode'],
                $item['vehicle_number'],
                $item['location'],
                $item['in_time'],
                $item['out_time'],
                $item['status'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'SlNo',
            'QR Code',
            'Vehicle Number',
            'Location',
            'In Time',
            'Out Time',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [

            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9D9D9'],
                ],
            ],
        ];
    }
}
