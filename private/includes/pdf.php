<?php
/**
 * Gerador de PDF minimalista e sem dependências externas.
 * Produz PDFs A4 com texto (Helvetica), linhas e tabelas simples.
 * Suporta acentuação portuguesa (conversão UTF-8 -> Windows-1252).
 */
class MhsPdf
{
    private array $pages = [];     // cada página é uma string de operadores de conteúdo
    private string $buf = '';      // página atual
    private float $w = 595.28;     // A4 largura (pt)
    private float $h = 841.89;     // A4 altura (pt)
    private float $margin = 48;
    private float $y;              // cursor vertical (do topo)

    public function __construct()
    {
        $this->addPage();
    }

    public function addPage(): void
    {
        if ($this->buf !== '') {
            $this->pages[] = $this->buf;
        }
        $this->buf = '';
        $this->y = $this->h - $this->margin;
    }

    private function enc(string $s): string
    {
        $s = iconv('UTF-8', 'Windows-1252//TRANSLIT', $s);
        return str_replace(['\\', '(', ')', "\r"], ['\\\\', '\\(', '\\)', ''], $s ?: '');
    }

    /** Garante espaço vertical; se não houver, abre nova página. */
    private function ensure(float $needed): void
    {
        if ($this->y - $needed < $this->margin) {
            $this->addPage();
        }
    }

    public function text(float $x, float $y, string $s, float $size = 11, bool $bold = false): void
    {
        $font = $bold ? '/F2' : '/F1';
        $this->buf .= "BT $font $size Tf 1 0 0 1 $x $y Tm (" . $this->enc($s) . ") Tj ET\n";
    }

    /** Escreve uma linha a fluir e baixa o cursor. */
    public function line_text(string $s, float $size = 11, bool $bold = false, float $lh = 16): void
    {
        $this->ensure($lh);
        $this->y -= $lh;
        $this->text($this->margin, $this->y, $s, $size, $bold);
    }

    public function space(float $px = 10): void
    {
        $this->y -= $px;
    }

    public function hr(): void
    {
        $this->ensure(8);
        $this->y -= 6;
        $this->buf .= "0.8 w 0.8 0.8 0.8 RG " . $this->margin . " " . $this->y . " m " . ($this->w - $this->margin) . " " . $this->y . " l S\n";
    }

    /** Caixa de título com fundo escuro. */
    public function title_band(string $titulo, string $sub = ''): void
    {
        $bandH = $sub !== '' ? 56 : 40;
        $top = $this->y;
        $this->buf .= "0.114 0.161 0.302 rg " . $this->margin . " " . ($top - $bandH) . " " . ($this->w - 2 * $this->margin) . " $bandH re f\n";
        $this->buf .= "1 1 1 rg\n";
        $this->text($this->margin + 14, $top - 26, $titulo, 17, true);
        if ($sub !== '') {
            $this->text($this->margin + 14, $top - 44, $sub, 10, false);
        }
        $this->buf .= "0 0 0 rg\n";
        $this->y = $top - $bandH - 14;
    }

    /** Par etiqueta/valor em duas colunas. */
    public function field(string $label, ?string $value, float $lh = 18): void
    {
        $this->ensure($lh);
        $this->y -= $lh;
        $this->text($this->margin, $this->y, $label, 9, true);
        $this->text($this->margin + 150, $this->y, ($value !== null && $value !== '') ? $value : '—', 11, false);
    }

    /** Tabela simples: cabeçalhos + linhas (array de arrays). $widths em fração. */
    public function table(array $headers, array $rows, array $widths): void
    {
        $tableW = $this->w - 2 * $this->margin;
        $cols = count($headers);
        $x0 = $this->margin;
        $rowH = 20;

        // Cabeçalho
        $this->ensure($rowH + 4);
        $this->y -= $rowH;
        $this->buf .= "0.90 0.93 0.98 rg $x0 " . $this->y . " $tableW $rowH re f 0 0 0 rg\n";
        $cx = $x0;
        for ($i = 0; $i < $cols; $i++) {
            $this->text($cx + 5, $this->y + 6, (string)$headers[$i], 9, true);
            $cx += $tableW * $widths[$i];
        }

        // Linhas
        foreach ($rows as $row) {
            $this->ensure($rowH);
            $this->y -= $rowH;
            $cx = $x0;
            for ($i = 0; $i < $cols; $i++) {
                $val = (string)($row[$i] ?? '');
                $this->text($cx + 5, $this->y + 6, $val, 9, false);
                $cx += $tableW * $widths[$i];
            }
            $this->buf .= "0.85 0.85 0.85 RG 0.5 w $x0 " . $this->y . " m " . ($x0 + $tableW) . " " . $this->y . " l S\n";
        }
    }

    public function footer_note(string $s): void
    {
        $this->text($this->margin, $this->margin - 18, $s, 8, false);
    }

    private function build(): string
    {
        // fechar página atual
        if ($this->buf !== '') {
            $this->pages[] = $this->buf;
            $this->buf = '';
        }
        $nPages = count($this->pages);

        $objs = [];
        // 1 catalog, 2 pages, depois N páginas + N conteúdos, depois 2 fontes
        $pageObjStart = 3;
        $contentObjStart = $pageObjStart + $nPages;
        $fontReg = $contentObjStart + $nPages;
        $fontBold = $fontReg + 1;

        $kids = [];
        for ($i = 0; $i < $nPages; $i++) {
            $kids[] = ($pageObjStart + $i) . ' 0 R';
        }

        $objs[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objs[2] = "<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count $nPages >>";

        for ($i = 0; $i < $nPages; $i++) {
            $pno = $pageObjStart + $i;
            $cno = $contentObjStart + $i;
            $objs[$pno] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$this->w} {$this->h}] "
                . "/Resources << /Font << /F1 $fontReg 0 R /F2 $fontBold 0 R >> >> /Contents $cno 0 R >>";
            $stream = $this->pages[$i];
            $objs[$cno] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream";
        }
        $objs[$fontReg]  = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
        $objs[$fontBold] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";

        ksort($objs);
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objs as $num => $body) {
            $offsets[$num] = strlen($pdf);
            $pdf .= "$num 0 obj\n$body\nendobj\n";
        }
        $xrefPos = strlen($pdf);
        $count = count($objs) + 1;
        $pdf .= "xref\n0 $count\n0000000000 65535 f \n";
        for ($i = 1; $i < $count; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size $count /Root 1 0 R >>\nstartxref\n$xrefPos\n%%EOF";
        return $pdf;
    }

    /** Envia o PDF como download para o browser. */
    public function download(string $filename): void
    {
        $pdf = $this->build();
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
    }

    /** Mostra o PDF inline (visualizar no browser). */
    public function inline(string $filename): void
    {
        $pdf = $this->build();
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
    }
}
