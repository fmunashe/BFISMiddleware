<?php

namespace App\Exports;

use App\Record;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RecordsExport implements FromCollection,withHeadings,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Record::all();
    }
    public function headings(): array
    {
        return [
            '#',
            'Batch Split ID',
            'Payment ID',
            'Record ID',
            'Corporate',
            'Debit Agent',
            'Debit Account',
            'Amount',
            'Currency',
            'Tran Type',
            'Beneficiary Name',
            'Beneficiary Account',
            'Crediting Agent',
            'Reference',
            'Received Date',
            'Processed Date',
            'Response',
            'Narration'
        ];
    }
}
