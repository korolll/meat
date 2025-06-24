<?php

namespace App\Services\Documents\Word;

use App\Models\User;
use App\Services\Framework\HasStaticMakeMethod;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class WordTemplate
{
    use HasStaticMakeMethod;

    /**
     * @var TemplateProcessor
     */
    protected $template;
    /**
     * @var User
     */
    protected $user;
    /**
     * @var
     */
    protected $path;

    /**
     * @var boolean
     */
    protected $isPDF;

    protected $errorMessage = '';

    /**
     * WordTemplate constructor.
     * @param string $templatePath
     * @param bool $isPDF
     */
    public function __construct(string $templatePath, bool $isPDF)
    {
        $this->isPDF = $isPDF;

        $this->template = app(TemplateProcessor::class, ['documentTemplate' => $templatePath]);
        $this->processTemplateAndSave();
        if ($this->isPDF) {
            $this->processWordToPDF();
        }
    }

    /**
     * @param string $name
     * @param array $headers
     * @return BinaryFileResponse
     */
    public function toResponse(string $name, array $headers = [])
    {
        if ($this->errorMessage) {
            throw new HttpException(500, $this->errorMessage);
        }

        return response()->download($this->path, $name, $headers, HeaderUtils::DISPOSITION_INLINE);
    }

    /**
     *
     */
    protected function processTemplateAndSave(): void
    {
        $this->processTemplate();
        $this->replaceVariables();

        $tempFile = tempnam(sys_get_temp_dir(), 'wrd');
        $this->template->saveAs($tempFile);

        $this->path = $tempFile;
    }

    protected function processWordToPDF(): void
    {
        $docPath = $this->path;
        if (!file_exists($docPath)) {
            throw new HttpException(500, 'docx file not found');
        }

        $userUUID = $this->user->uuid;
        $pdfTempDir = sys_get_temp_dir() . "/pdf_{$userUUID}";

        if (!is_dir($pdfTempDir) && !mkdir($pdfTempDir) && !is_dir($pdfTempDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $pdfTempDir));
        }

        $fileName = File::name($docPath);

        $convertCmd = "libreoffice --headless --convert-to pdf {$docPath} --outdir {$pdfTempDir}";
        exec($convertCmd);

        $pdfFilePath = "{$pdfTempDir}/{$fileName}.pdf";
        if (file_exists($pdfFilePath)) {
            $this->path = $pdfFilePath;
        } else {
            $this->errorMessage = 'Ошибка конвертации docx -> pdf';
        }
    }

    /**
     *
     */
    protected function replaceVariables(): void
    {
        $variables = $this->getVariables();
        foreach ($variables as $variable => $value) {
            $value = $value ?: '';
            $this->template->setValue($variable, $value);
        }
    }

    /**
     *
     */
    abstract protected function processTemplate(): void;

    /**
     * @return array
     */
    abstract protected function getVariables(): array;
}
