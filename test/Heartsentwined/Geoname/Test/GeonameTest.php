<?php
namespace Heartsentwined\Geoname\Test;

use Heartsentwined\FileSystemManager\FileSystemManager;
use Heartsentwined\Geoname\Entity;
use Heartsentwined\Geoname\Repository;
use Heartsentwined\Geoname\Service\Geoname;
use Heartsentwined\Phpunit\Testcase\Doctrine as DoctrineTestcase;

class GeonameTest extends DoctrineTestcase
{
    public function setUp()
    {
        $this
            ->setBootstrap(__DIR__ . '/../../../../bootstrap.php')
            ->setEmAlias('doctrine.entitymanager.orm_default')
            ->setTmpDir('tmp');
        parent::setUp();

        if (!is_dir('tmp/geoname')) mkdir('tmp/geoname', 0755, true);
        $this->geoname = $this->sm->get('geoname')
            ->setEm($this->em)
            ->setTmpDir('tmp/geoname');
    }

    public function tearDown()
    {
        unset($this->geoname);
        FileSystemManager::rrmdir('tmp/goename');
        parent::tearDown();
    }

    public function getCliDummy()
    {
        $cli = $this->sm->get('Heartsentwined\Cli\Cli')
            ->setTemplates(array(
                'section' => array(
                    'template'  => '## %s ##',
                    'color'     => 'YELLOW',
                ),
                'task' => array(
                    'template'  => '- %s -',
                    'color'     => 'BLUE',
                ),
                'module' => array(
                    'template'  => '[ %s ]',
                    'color'     => 'GREEN',
                ),
            ));
        return $cli;
    }

    public function testGetMeta()
    {
        $metaRepo = $this->em
            ->getRepository('Heartsentwined\Geoname\Entity\Meta');

        // no meta in the beginning
        $this->assertCount(0, $metaRepo->findAll());

        // no meta -> create one, with 'install_download' status
        $meta = $this->geoname->getMeta();
        $this->assertSame(Repository\Meta::STATUS_INSTALL_DOWNLOAD,
            $meta->getStatus());
        $this->assertCount(1, $metaRepo->findAll());

        // get again -> retrieve same meta
        $meta = $this->geoname->getMeta();
        $this->assertSame(Repository\Meta::STATUS_INSTALL_DOWNLOAD,
            $meta->getStatus());
        $this->assertCount(1, $metaRepo->findAll());

        // clean the meta -> get = create another one
        $this->em->remove($meta);
        $this->em->flush();
        $meta = $this->geoname->getMeta();
        $this->assertSame(Repository\Meta::STATUS_INSTALL_DOWNLOAD,
            $meta->getStatus());
        $this->assertCount(1, $metaRepo->findAll());

        // change meta status -> get meta -> don't update it
        $meta->setStatus(Repository\Meta::STATUS_UPDATE);
        $this->em->flush();
        $meta = $this->geoname->getMeta();
        $this->assertSame(Repository\Meta::STATUS_UPDATE,
            $meta->getStatus());
        $this->assertCount(1, $metaRepo->findAll());
    }
    public function testdownloadFile()
    {
        // download file and save to tmp/foo
        $this->geoname->downloadFile('http://www.google.com', 'tmp/foo');
        $this->assertFileExists('tmp/foo');
        $this->assertFalse(file_exists('tmp/foo.lock'));
        $this->assertFalse(file_exists('tmp/foo.done'));

        // don't download again
        $mtime = filemtime('tmp/foo');
        $this->geoname->downloadFile('http://www.google.com', 'tmp/foo');
        $this->assertFileExists('tmp/foo');
        $this->assertFalse(file_exists('tmp/foo.lock'));
        $this->assertFalse(file_exists('tmp/foo.done'));
        $this->assertSame($mtime, filemtime('tmp/foo'));

        // don't download if .lock
        rename('tmp/foo', 'tmp/foo.lock');
        $this->geoname->downloadFile('http://www.google.com', 'tmp/foo');
        $this->assertFalse(file_exists('tmp/foo'));
        $this->assertFileExists('tmp/foo.lock');
        $this->assertFalse(file_exists('tmp/foo.done'));

        // don't download if .done
        rename('tmp/foo.lock', 'tmp/foo.done');
        $this->geoname->downloadFile('http://www.google.com', 'tmp/foo');
        $this->assertFalse(file_exists('tmp/foo'));
        $this->assertFalse(file_exists('tmp/foo.lock'));
        $this->assertFileExists('tmp/foo.done');

        // don't download if 404
        $this->geoname->downloadFile('http://example.test', 'tmp/bar');
        $this->assertFalse(file_exists('tmp/bar'));
    }

    public function testDownloadUpdate()
    {
        $now = \DateTime::createFromFormat('U', strtotime('-1 day'));
        $date = $now->format('Y-m-d');
        foreach(array(
            "http://download.geonames.org/export/dump/modifications-$date.txt",
            "http://download.geonames.org/export/dump/deletes-$date.txt",
            "http://download.geonames.org/export/dump/alternateNamesModifications-$date.txt",
            "http://download.geonames.org/export/dump/alternateNamesDeletes-$date.txt",
        ) as $dir => $url) {
            $headers = get_headers($url);
            $this->assertSame(0, preg_match('/40\d/', $headers[0]));
        }

        $geoname = $this->getMock(
            'Heartsentwined\Geoname\Service\Geoname',
            array('downloadFile'));
        $geoname
            ->expects($this->exactly(4))
            ->method('downloadFile');
        $geoname
            ->setCli($this->getCliDummy())
            ->setEm($this->em)
            ->setTmpDir('tmp/geoname')
            ->downloadUpdate();
    }

    public function testInstallDownload()
    {
        foreach(array(
            'http://download.geonames.org/export/dump/countryInfo.txt',
            'http://download.geonames.org/export/dump/featureCodes_en.txt',
            'http://download.geonames.org/export/dump/timeZones.txt',
            'http://download.geonames.org/export/dump/hierarchy.zip',
            'http://download.geonames.org/export/dump/alternateNames.zip',
            'http://download.geonames.org/export/dump/allCountries.zip',
        ) as $dir => $url) {
            $headers = get_headers($url);
            $this->assertSame(0, preg_match('/40\d/', $headers[0]));
        }

        $geoname = $this->getMock(
            'Heartsentwined\Geoname\Service\Geoname',
            array('downloadFile'));
        $geoname
            ->expects($this->exactly(6))
            ->method('downloadFile');
        $geoname
            ->setCli($this->getCliDummy())
            ->setEm($this->em)
            ->setTmpDir('tmp/geoname')
            ->installDownload();

        $this->assertSame(Repository\Meta::STATUS_INSTALL_PREPARE,
            $geoname->getMeta()->getStatus());
    }

    public function testInstallPrepare()
    {
        $fh = fopen('tmp/geoname/allCountries.txt', 'a+');
        for ($i=1; $i<=50001; $i++) {
            fwrite($fh, "a\n");
        }
        fclose($fh);
        touch('tmp/geoname/alternateNames.txt');
        touch('tmp/geoname/hierarchy.txt');

        // dummy zip files
        touch('tmp/geoname/foo');
        foreach (array(
            'tmp/geoname/allCountries.zip',
            'tmp/geoname/alternateNames.zip',
            'tmp/geoname/hierarchy.zip',
        ) as $file) {
            $zip = new \ZipArchive();
            $zip->open($file, \ZipArchive::CREATE);
            $zip->addFile('tmp/geoname/foo');
            $zip->close();
            $this->assertFileExists($file);
        }

        $this->geoname->installPrepare();
        $this->assertCount(3,
            FileSystemManager::fileIterator('tmp/geoname/allCountries'));
        $this->assertFileExists('tmp/geoname/allCountries/1');
        $this->assertFileExists('tmp/geoname/allCountries/25001');
        $this->assertFileExists('tmp/geoname/allCountries/50001');

        foreach (array(
            'tmp/geoname/allCountries/1',
            'tmp/geoname/allCountries/25001',
        ) as $file) {
            $lineCount = 0;
            $fh = fopen($file, 'r');
            while ($line = fgets($fh)) {
                if (in_array($lineCount % 25000, array(0, 1, 24999))) {
                    $this->assertSame("a\n", $line);
                }
                $lineCount++;
            }
            fclose($fh);
            $this->assertSame(25000, $lineCount);
        }

        $lineCount = 0;
        $fh = fopen('tmp/geoname/allCountries/50001', 'r');
        while ($line = fgets($fh)) {
            if (in_array($lineCount % 25000, array(0, 1, 24999))) {
                $this->assertSame("a\n", $line);
            }
            $lineCount++;
        }
        fclose($fh);
        $this->assertSame(1, $lineCount);

        $this->assertSame(Repository\Meta::STATUS_INSTALL_LANGUAGE,
            $this->geoname->getMeta()->getStatus());
    }
}
