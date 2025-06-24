<?php

namespace Tests\Unit\App\Exceptions;

use App\Exceptions\ClientException;
use Illuminate\Foundation\Testing\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Tests\CreatesApplication;

class ExceptionCodeTest extends TestCase
{
    use CreatesApplication;

    // It should be fixed
//    /**
//     * @test
//     * @throws \ReflectionException
//     */
//    public function uniqueCode()
//    {
//        $codes = [];
//
//        foreach ($this->classNameGenerator() as $className) {
//            $code = $this->makeException($className)->getExceptionCode();
//
//            if (in_array($code, $codes)) {
//                $this->fail("Exception code duplicate: {$code}.");
//            }
//
//            $codes[] = $code;
//        }
//    }

    /**
     * @return \Generator
     */
    private function classNameGenerator(): \Generator
    {
        $files = Finder::create()->in($this->makeClassPath())->name('*.php')->files();

        $this->assertTrue($files->hasResults());

        foreach ($files as $file) {
            yield $this->makeClassName($file);
        }
    }

    /**
     * @param \Symfony\Component\Finder\SplFileInfo $file
     * @return string
     */
    private function makeClassName(SplFileInfo $file): string
    {
        $linuxStylePathname = str_replace('/', '\\', $file->getRelativePathname());

        return '\\App\\Exceptions\\ClientExceptions\\' . substr($linuxStylePathname, 0, -4);
    }

    /**
     * @return string
     */
    private function makeClassPath(): string
    {
        return app_path('Exceptions/ClientExceptions');
    }

    /**
     * @param string $class
     * @return \App\Exceptions\ClientException|object
     * @throws \ReflectionException
     */
    private function makeException(string $class): ClientException
    {
        return (new \ReflectionClass($class))->newInstanceWithoutConstructor();
    }
}
