<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class DompdfService
{
    private $dompdf;

    public function __construct()
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $this->dompdf = new Dompdf($options);
    }

    /**
     * Generate a PDF from HTML.
     *
     * @param string 
     * @param string
     * @return string 
     */
    public function generatePdf(string $html, string $filename = 'document.pdf'): string
    {
        $css = '
            /* General styles */
        body {
            font-family: "Arial", sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
        }

        /* Heading styles */
        h1 {
            font-size: 2.5rem;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        h5 {
            font-size: 1.5rem;
            color: #34495e;
            margin-bottom: 0.75rem;
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.5rem;
        }

        /* List styles */
        ul {
            list-style-type: none;
            padding-left: 0;
        }

        li {
            background-color: #ecf0f1;
            margin: 0.5rem 0;
            padding: 0.75rem;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        li:hover {
            background-color: #3498db;
            color: #fff;
            transform: translateX(10px);
            transition: all 0.3s ease;
        }

        /* Card styles */
        .card {
            background-color: #fff;
            border-radius: 10px;
            max-width: 800px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        ';

        $html = "<style>{$css}</style>" . $html;

        $this->dompdf->loadHtml($html);

        $this->dompdf->setPaper([0, 0, 1100, 1414], 'portrait');

        $this->dompdf->render();

        return $this->dompdf->output();
    }
}
