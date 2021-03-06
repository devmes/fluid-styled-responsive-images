<?php
namespace Schnitzler\FluidStyledResponsiveImages\Resource\Rendering;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Tests\AccessibleObjectInterface;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class ImageRendererTest
 * @package Schnitzler\FluidStyledResponsiveImages\Resource\Rendering
 */
class ImageRendererTest extends UnitTestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AccessibleObjectInterface|ImageRendererConfiguration
     */
    protected $imageRendererConfiguration;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AccessibleObjectInterface|ImageRenderer
     */
    protected $imageRenderer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AccessibleObjectInterface|File
     */
    protected $file;

    /**
     * @var array
     */
    protected $processedFiles = [];

    /**
     * @return void
     */
    public function setUp()
    {
        $this->setUpProcessedFiles();

        $this->file = $this->getAccessibleMock(
            File::class,
            ['getProperty', 'process'],
            [],
            '',
            false
        );

        $this->file
            ->expects($this->any())
            ->method('getProperty')
            ->will($this->returnCallback(function ($in) {
                switch ($in) {
                    case 'title':
                        return 'title';
                        break;
                    case 'alternative':
                        return 'alt';
                        break;
                    default:
                        return '';
                        break;
                }
            }));

        $this->imageRendererConfiguration = $this->getMock(
            ImageRendererConfiguration::class,
            [],
            [],
            '',
            false
        );

        $this->imageRendererConfiguration
            ->expects($this->any())
            ->method('getAbsRefPrefix')
            ->will($this->returnValue(''));

        $this->imageRendererConfiguration
            ->expects($this->any())
            ->method('getGenericTagAttributes')
            ->will($this->returnValue([]));

        $this->imageRenderer = $this->getMock(
            ImageRenderer::class,
            ['getConfiguration']
        );

        $this->imageRenderer
            ->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($this->imageRendererConfiguration));
    }

    /**
     * @return void
     */
    public function setUpProcessedFiles()
    {
        $processedFile = $this->getMock(
            ProcessedFile::class,
            ['getPublicUrl', 'getProperty'],
            [],
            '',
            false
        );

        $processedFile
            ->expects($this->any())
            ->method('getPublicUrl')
            ->will($this->returnValue('image.jpg'));

        $processedFile
            ->expects($this->any())
            ->method('getProperty')
            ->will($this->returnValue(100));

        $this->processedFiles[0] = $processedFile;

        $processedFile = $this->getMock(
            ProcessedFile::class,
            ['getPublicUrl'],
            [],
            '',
            false
        );

        $processedFile
            ->expects($this->any())
            ->method('getPublicUrl')
            ->will($this->returnValue('image360.jpg'));

        $this->processedFiles[1] = $processedFile;

        $processedFile = $this->getMock(
            ProcessedFile::class,
            ['getPublicUrl'],
            [],
            '',
            false
        );

        $processedFile
            ->expects($this->any())
            ->method('getPublicUrl')
            ->will($this->returnValue('image720.jpg'));

        $this->processedFiles[2] = $processedFile;
    }

    /**
     * @return void
     */
    public function testWithSrcSetAndWithoutSourceCollection()
    {
        $this->file
            ->expects($this->at(1))
            ->method('process')
            ->will($this->returnValue($this->processedFiles[0]));

        $this->imageRendererConfiguration
            ->expects($this->once())
            ->method('getSourceCollection')
            ->will($this->returnValue([]));

        $this->imageRendererConfiguration
            ->expects($this->once())
            ->method('getLayoutKey')
            ->will($this->returnValue('srcset'));

        $result = $this->imageRenderer->render(
            $this->file,
            '1000',
            '1000',
            []
        );

        $this->assertEquals(
            '<img src="image.jpg" alt="alt" title="title" />',
            $result,
            'sizes-attribute is omitted when no sizes are given'
        );
    }

    /**
     * @return void
     */
    public function testWithSrcSetAndSourceCollection()
    {
        $this->file
            ->expects($this->at(1))
            ->method('process')
            ->will($this->returnValue($this->processedFiles[1]));

        $this->file
            ->expects($this->at(2))
            ->method('process')
            ->will($this->returnValue($this->processedFiles[2]));

        $this->file
            ->expects($this->at(3))
            ->method('process')
            ->will($this->returnValue($this->processedFiles[0]));

        $this->imageRendererConfiguration
            ->expects($this->once())
            ->method('getSourceCollection')
            ->will($this->returnValue([
                10 => [
                    'width' => '360m',
                    'srcset' => '360w',
                ],
                20 => [
                    'width' => '720m',
                    'srcset' => '720w',
                    'sizes' => '(min-width: 360px) 720px',
                ]
            ]));

        $this->imageRendererConfiguration
            ->expects($this->once())
            ->method('getLayoutKey')
            ->will($this->returnValue('srcset'));

        $this->assertEquals(
            '<img src="image.jpg" alt="alt" title="title" srcset="image360.jpg 360w, image720.jpg 720w" sizes="(min-width: 360px) 720px" />',
            $this->imageRenderer->render(
                $this->file,
                '1000',
                '1000',
                []
            )
        );
    }

    /**
     * @return void
     */
    public function testWithDataAndWithoutSourceCollection()
    {
        $this->file
            ->expects($this->at(1))
            ->method('process')
            ->will($this->returnValue($this->processedFiles[0]));

        $this->imageRendererConfiguration
            ->expects($this->once())
            ->method('getSourceCollection')
            ->will($this->returnValue([]));

        $this->imageRendererConfiguration
            ->expects($this->once())
            ->method('getLayoutKey')
            ->will($this->returnValue('data'));

        $this->assertEquals(
            '<img src="image.jpg" alt="alt" title="title" />',
            $this->imageRenderer->render(
                $this->file,
                '1000',
                '1000',
                []
            )
        );
    }

    /**
     * @return void
     */
    public function testWithDataAndSourceCollection()
    {
        $this->file
            ->expects($this->at(1))
            ->method('process')
            ->will($this->returnValue($this->processedFiles[1]));

        $this->file
            ->expects($this->at(2))
            ->method('process')
            ->will($this->returnValue($this->processedFiles[2]));

        $this->file
            ->expects($this->at(3))
            ->method('process')
            ->will($this->returnValue($this->processedFiles[0]));

        $this->imageRendererConfiguration
            ->expects($this->once())
            ->method('getSourceCollection')
            ->will($this->returnValue([
                10 => [
                    'width' => '360m',
                    'dataKey' => 'small',
                ],
                20 => [
                    'width' => '720m',
                    'dataKey' => 'small-retina',
                ]
            ]));

        $this->imageRendererConfiguration
            ->expects($this->once())
            ->method('getLayoutKey')
            ->will($this->returnValue('data'));

        $this->assertEquals(
            '<img src="image.jpg" alt="alt" title="title" data-small="image360.jpg" data-small-retina="image720.jpg" />',
            $this->imageRenderer->render(
                $this->file,
                '1000',
                '1000',
                []
            )
        );
    }

}
