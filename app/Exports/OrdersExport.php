<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithStyles,
    WithEvents,
    ShouldAutoSize,
    WithTitle,
    WithColumnWidths
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;




class OrdersExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithEvents,
    // ShouldAutoSize,
    // WithTitle,
    WithDrawings,
    WithCustomStartCell,
    WithColumnWidths
{

    protected $data;
    protected $headers;

    public function __construct(Collection $data, array $headers = [])
    {
        $this->data = $data;
        $this->headers = $headers;
    }

    public function startCell(): string
    {
        return 'A9';
    }



    public function headings(): array
    {
        return $this->headers ?: [];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = 10 + count($this->data);
        $bodyRange = 'A9:F'.$lastRow;
        return [
            9    => ['font' => ['bold' => true,'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0070c0'],
            ]],



        ];
    }

    // public function title(): string
    // {
    //     return 'Orders Summary';
    // }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Merge header cells
                $sheet->setCellValue('B2', 'Status:')->getStyle('B2')->applyFromArray([
                   'font' => ['bold' => true,'color' => ['argb' => 'FFFFFFFF']], 'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0070c0'],
            ]
                ]);
                $sheet->setCellValue('B5', 'Date:')->getStyle('B5')->applyFromArray([
                   'font' => ['bold' => true,'color' => ['argb' => 'FFFFFFFF']], 'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0070c0'],
            ]
                ]);
                $sheet->setCellValue('D2', 'Search Text:')->getStyle('D2')->applyFromArray([
                   'font' => ['bold' => true,'color' => ['argb' => 'FFFFFFFF']], 'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0070c0'],
            ]
                ]);
                $sheet->setCellValue('D5', 'No. Of Records:')->getStyle('D5')->applyFromArray([
                   'font' => ['bold' => true,'color' => ['argb' => 'FFFFFFFF']], 'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0070c0'],
            ]
                ]);

                // Style headers
                $lastRow = count($this->data);
                $bodyRange = 'A9:G'.$lastRow+9;
                $sheet->getStyle($bodyRange)->applyFromArray([
                    'alignment' => ['horizontal' => 'center'],
                ]);

                // // Style column headings
                // $sheet->getStyle('B9:O9')->applyFromArray([
                //     'font' => ['bold' => true],
                //     'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFE6E6E6']],
                //     'borders' => ['allBorders' => ['borderStyle' => 'thin']],
                // ]);

                // // Style all data rows (basic)
                // $lastRow = 9 + count($this->data);
                // $sheet->getStyle("B10:O$lastRow")->applyFromArray([
                //     'borders' => ['allBorders' => ['borderStyle' => 'thin']],
                // ]);
            }
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 25,
            'C' => 25,
            'D' => 25,
            'E' => 25,
            'F' => 25,
            'G' => 25,
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('PizzaShop Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath(public_path('brandLogo.png')); // your logo path
        $drawing->setHeight(80);
        $drawing->setCoordinates('G2');

        return [$drawing];
    }

    public function collection()
    {
        return $this->data;
    }
}
