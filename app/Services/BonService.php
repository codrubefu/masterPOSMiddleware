<?php

namespace App\Services;

class BonService
{
    protected $casaFiles;
    protected $templatePath;

    public function __construct()
    {
        $this->casaFiles = config('casa.file');
        $this->templatePath = __DIR__ . '/BonExample/';
    }

    /**
     * Get the next bon number and increment the stored value.
     *
     * @return int
     * @throws \Exception
     */
    public function getNextBonNumber(): int
    {
        $bonFilePath = base_path('bon');

        if (!file_exists($bonFilePath)) {
            throw new \Exception('Bon file does not exist.');
        }

        $bonNo = file_get_contents($bonFilePath);
        if ($bonNo === false) {
            throw new \Exception('Failed to read bon file.');
        }

        $currentBon = intval($bonNo);
        $nextBon = $currentBon + 1;

        if (file_put_contents($bonFilePath, $nextBon) === false) {
            throw new \Exception('Failed to update bon file.');
        }

        return $nextBon;
    }

    /**
     * Load template content from file
     *
     * @param string $templateFileName
     * @return string
     * @throws \Exception
     */
    private function loadTemplate(string $templateFileName): string
    {
        $templateFile = $this->templatePath . $templateFileName;
        
        if (!file_exists($templateFile)) {
            throw new \Exception("Template file not found: {$templateFileName}");
        }
        
        $content = file_get_contents($templateFile);
        
        if ($content === false) {
            throw new \Exception("Failed to read template file: {$templateFileName}");
        }
        
        // Convert escape sequences like \t to actual characters
        return stripcslashes($content);
    }

    /**
     * Write formatted content to bon file
     *
     * @param int $casa
     * @param string $content
     * @return bool
     * @throws \Exception
     */
    private function writeToBonFile(int $casa, string $content): bool
    {
        $entryFilePath = $this->casaFiles[$casa]['path'] . '/bon.txt';
        
        if (file_put_contents($entryFilePath, $content) === false) {
            throw new \Exception('Failed to write to bon file.');
        }

        return true;
    }

    /**
     * Write new entry line (product with name, quantity, price)
     *
     * @param int $casa
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public function writeNewEntry($casa, $data): void
    {
        $template = $this->loadTemplate('bonline.txt');
        $content = sprintf($template, $data['name'], $data['quantity'], $data['price']);
        $this->writeToBonFile($casa, $content);
    }

    /**
     * Write subtotal line
     *
     * @param int $casa
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public function writeSubtotal( $data): void
    {
        $template = $this->loadTemplate('subtotal.txt');
        $content = sprintf($template, $data['subtotal']);
        $this->writeToBonFile($data['casa'], $content);
    }

    public function writeBonFinal($data): int
    {
        $template = $this->loadTemplate('bonfinal.txt');
        $lines = [];
        if($data['type']=='cash'){
            $code = 0;
        }
        elseif($data['type']=='card'){
            $code = 1;
        }
        else{
            $code = 2;
        }
        foreach ($data['items'] as $item) {
            $lines[] = sprintf(
                "49,%s\t%s\t%s\t%s\t\t\t%s\tbuc\t",
                $item['product']['name'],
                $item['qty'],
                $item['unitPrice'],
                '1.0000',
                 $item['qty'],
            );
        }
        $itemsContent = implode("\n", $lines);
        $finalContent = sprintf($template, $itemsContent,$code);
        return $this->writeToBonFile($data['casa'], $finalContent);
         
    }

}
