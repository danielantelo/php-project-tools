Php Project Tools
=================

Composer based project tools to automate the following:

- Check php files for syntax errors
- Check php files for PSR Coding Standads
- Check php files for possible bugs, unused parameters, suboptimal code, etc.
- Ensure all project tests are being passed

**INSTALLATION:**

Simply add daa/project-tools as a composer dependency.

```
# composer.json

    "require-dev": {
        ...
        "daa/project-tools": "~1.0"
    }
```

**USAGE:**

If you are using git for your project use the scripts provided to configure the automate checks.

```
# composer.json

    "scripts": {
        "post-update-cmd": "Project\\Script\\GitHooks::setup",
        "post-install-cmd": "Project\\Script\\GitHooks::setup"
    }
```

After a composer update --dev, when ever you carry out a git commit, it will ensure there are no errors, coding standard issues or failing tests before processing the commit.

Note: May not work in some IDEs (eg. git hooks are ignored in Netbeans).

You can configure the the pre-commit rules by modifying the $conf array in .git/hooks/pre-commit, the defaults are:

```
$conf = array(
    'excludeTests' => false,
    'codingStandard' => 'PSR2',
    'messRules' => 'controversial',
    ''
);
```

and advanced configuration can be:

```
$conf = array(
    'excludeTests' => true,
    'codingStandard' => array('PSR2', 'symfony2'),
    'messRules' => array('controversial', 'codesize', 'unusedcode'),
    'customChecks' => array(
        array('cmd' => 'scss-lint', 'ext' => 'css'),
        array('cmd' => 'jscs --preset=jquery', 'ext' => 'js')
    )
);
```



**ALTERNATIVE USE:**

If you are not using git or don't want the checks to be automated hooks, you can use the tools manually.

```
use Project\Tool\CodeQualityTool;

// check an entire composer project
$tool = new CodeQualityTool();
$tool->run();

// check an entire composer project but without executing tests
$tool = new CodeQualityTool();
$tool->excludeTests();
$tool->run();

// check a set of files
$files = array('file1.php', 'file2.php');
$tool = new CodeQualityTool($files);
$tool->run();

// check a set of files without executing tests
$files = array('file1.php', 'file2.php');
$tool = new CodeQualityTool($files, true);
$tool->run();
```

You can also use individual modules

```
use Project\Tool\Checker\SyntaxErrorChecker;
use Project\Tool\Checker\CodingStandardsChecker;

// example of how to use a checker to check whole project
$checker = new SyntaxErrorChecker($projectDir);
if (!$checker->check()) {
    throw new \Exception('There are syntax errors!');
}

// example of how to use a checker to check a set of files
$files = array('file1.php', 'file2.php');
$checker = new SyntaxErrorChecker($projectDir);
if (!$checker->check($files)) {
    throw new \Exception('There are syntax errors!');
}
```

Have a look at Hooks/git/pre-commit and Tools/CodeQualityTool.php for more usage information.

