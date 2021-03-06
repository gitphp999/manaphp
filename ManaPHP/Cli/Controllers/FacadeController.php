<?php
namespace ManaPHP\Cli\Controllers;

use ManaPHP\Cli\Controller;

/**
 * Class ManaPHP\Cli\Controllers\FacadeController
 *
 * @package ManaPHP\Cli\Controllers
 */
class FacadeController extends Controller
{
    /**
     * @CliCommand create helper of framework facade
     * @throws \ManaPHP\Cli\Controllers\Exception
     */
    public function frameworkCommand()
    {
        $content = <<<EOD
<?php
namespace ManaPHP\Facade;

use ManaPHP\Facade;
class Exception extends \ManaPHP\Exception{}

EOD;
        foreach ($this->filesystem->glob('@manaphp/Facade/*.php') as $file) {
            $facadeName = pathinfo($file, PATHINFO_FILENAME);
            $lines = $this->filesystem->fileGet($file);
            if (preg_match('#static\s+(.*)\s+getFacadeInstance*#', $lines, $match) !== 1) {
                continue;
            }

            if (strpos($match[1], '\\') === false) {
                $interfaceName = $match[1];
                if (preg_match('#use\s+(.*' . $interfaceName . ')#', $lines, $match) !== 1) {
                    throw new Exception('`:interface` interface is not invalid.', ['interface' => $interfaceName]);
                }

                $interfaceName = ($match[1] === '\\' ? '' : '\\') . $match[1];
            } else {
                $interfaceName = $match[1];
            }

            $r = $this->generate($facadeName, $interfaceName);
            $this->console->writeLn(str_pad(' ' . $facadeName . ':', 16, ' ') . $interfaceName);
            $content .= PHP_EOL . PHP_EOL . $r;
        }

        $this->filesystem->filePut('@manaphp/.ide.helper.facade.php', $content);
    }

    /**
     * @param string $facadeClassName
     * @param string $interfaceName
     *
     * @return mixed
     */
    public function generate($facadeClassName, $interfaceName)
    {
        $content = <<<EOD

/**
  * @method  static $interfaceName getFacadeInstance()
  */
class $facadeClassName extends Facade
{

EOD;
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $rc = new \ReflectionClass($interfaceName);
        $lines = file($rc->getFileName());
        foreach ($rc->getMethods() as $method) {
            $comment = '    ' . $method->getDocComment();
            $content .= $comment . PHP_EOL;

            $signature = '';
            for ($i = $method->getStartLine(); $i <= $method->getEndLine(); $i++) {
                $signature .= ' ' . $lines[$i - 1];
            }
            $signature = rtrim(rtrim($signature), ';') . '{}' . PHP_EOL . PHP_EOL;
            $content .= preg_replace('#public\s+function#', 'public static function', $signature);
        }
        $content = rtrim($content) . PHP_EOL . '}';

        return $content;
    }
}