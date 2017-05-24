<?php


use Arc\Filesystem\File;
use Arc\Filesystem\FileManager;

class FileManagerTest extends FrameworkTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fileManager = new FileManager();
    }

    /** @test */
    public function the_get_all_files_in_directory_method_returns_all_files_as_file_objects()
    {
        $testDirectory = '/tmp/filesystem_test';

        if (file_exists($testDirectory)) {
            $this->fileManager->deleteDirectory($testDirectory);
        }

        mkdir($testDirectory);

        $testFile = "$testDirectory/testFile.txt";
        $fh = fopen($testFile, 'w');
        fwrite($fh, 'text');
        fclose($fh);

        $files = $this->fileManager->getAllFilesInDirectory($testDirectory);

        $this->assertEquals(1, count($files));
        $this->assertTrue(array_pop($files) instanceof File);
    }

    /** @test */
    public function the_create_fresh_directory_method_creates_a_directory_if_it_does_not_exist()
    {
        $this->fileManager->deleteDirectory('/tmp/fresh');

        $this->assertFalse(file_exists('/tmp/fresh'));
        $this->fileManager->createFreshDirectory('/tmp/fresh');
        $this->assertTrue(file_exists('/tmp/fresh'));
    }
}
