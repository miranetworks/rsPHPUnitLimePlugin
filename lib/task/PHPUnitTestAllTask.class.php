<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Launches all tests.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfTestAllTask.class.php 25036 2009-12-07 19:41:58Z Kris.Wallsmith $
 */
class PHPUnitTestAllTask extends BasePHPUnitTestTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    parent::configure();
    
    $this->addOptions(array(
      new sfCommandOption('only-failed', 'f', sfCommandOption::PARAMETER_NONE, 'Only run tests that failed last time'),
      new sfCommandOption('xml', null, sfCommandOption::PARAMETER_REQUIRED, 'The file name for the JUnit compatible XML log file'),
    ));

    $this->aliases = array('test-phpunit-all');
    $this->name = 'phpunit-all';
    $this->briefDescription = 'Launches all tests';

    $this->detailedDescription = <<<EOF
The [test:all|INFO] task launches all unit and functional tests:

  [./symfony test:all|INFO]

The task launches all tests found in [test/|COMMENT].

If some tests fail, you can use the [--trace|COMMENT] option to have more
information about the failures:

    [./symfony test:all -t|INFO]

Or you can also try to fix the problem by launching them by hand or with the
[test:unit|COMMENT] and [test:functional|COMMENT] task.

Use the [--only-failed|COMMENT] option to force the task to only execute tests
that failed during the previous run:

    [./symfony test:all --only-failed|INFO]

Here is how it works: the first time, all tests are run as usual. But for
subsequent test runs, only tests that failed last time are executed. As you
fix your code, some tests will pass, and will be removed from subsequent runs.
When all tests pass again, the full test suite is run... you can then rinse
and repeat.

The task can output a JUnit compatible XML log file with the [--xml|COMMENT]
options:

  [./symfony test:all --xml=log.xml|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $coverage = null;
    
    require_once dirname(__FILE__).'/../test/PHPUnitLimeHarness.class.php';

    $h = new PHPUnitLimeHarness(array(
      'force_colors' => isset($options['color']) && $options['color'],
      'verbose'      => isset($options['trace']) && $options['trace'],
    ));
    $h->addPlugins(array_map(array($this->configuration, 'getPluginConfiguration'), $this->configuration->getPlugins()));
    $h->base_dir = sfConfig::get('sf_test_dir');

    $status = false;
    $statusFile = sfConfig::get('sf_cache_dir').'/.test_all_status';
    if ($options['only-failed'])
    {
      if (file_exists($statusFile))
      {
        $status = unserialize(file_get_contents($statusFile));
      }
    }

    if ($status)
    {
      foreach ($status as $file)
      {
        $h->register($file);
      }
    }
    else
    {
      // filter and register all tests
      $finder = sfFinder::type('file')->follow_link()->name('*Test.php');
      $h->register($this->filterTestFiles($finder->in($h->base_dir), $arguments, $options));
    }

    if(($options['coverage-html'] || $options['coverage-clover']))
    {
      $coverage = new LimeCodeCoverage();
      $coverage->setFolder($options['coverage-folder']);
    }

    $ret = $h->run($coverage,($options['coverage-html'] || $options['coverage-clover'])) ? 0 : 1;

    $this->flushLogs($options,$coverage,$h);

    return $ret;
  }
}
