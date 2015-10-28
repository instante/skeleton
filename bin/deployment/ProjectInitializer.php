<?php
namespace Instante\Deployment;


use Nette\InvalidStateException;
use Nette\Neon\Neon;
use Nette\Utils\Json;

class ProjectInitializer
{
    /** @var string */
    private $dir;

    /** @var string */
    private $webmasterEmail;
    /** @var string */
    private $projectName;
    /** @var string */
    private $projectDescription;
    /** @var string */
    private $projectLicense;
    /** @var string */
    private $projectVersion;
    /** @var string */
    private $authorName;
    /** @var string */
    private $authorEmail;
    /** @var array */
    private $errors = [];

    /**
     * ProjectInitializer constructor.
     * @param string $dir
     */
    public function __construct($dir) { $this->dir = $dir; }

    public function checkProjectConfigured()
    {
        return file_exists($this->dir . '/app/config/environment');
    }

    /**
     * @param string $webmasterEmail \
     * @return $this
     */
    public function setWebmasterEmail($webmasterEmail)
    {
        $this->webmasterEmail = $webmasterEmail;
        return $this;
    }

    /**
     * @param string $name
     * @param string $description
     * @param string $license
     * @param string $version
     * @return $this
     */
    public function setProjectMeta($name, $description, $license, $version)
    {
        $this->projectName = $name;
        $this->projectDescription = $description;
        $this->projectLicense = $license;
        $this->projectVersion = $version;
        return $this;
    }

    /**
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setAuthor($name, $email)
    {
        $this->authorName = $name;
        $this->authorEmail = $email;
        return $this;
    }

    /** @return array */
    public function getErrors()
    {
        return $this->errors;
    }

    public function initialize()
    {
        if ($this->checkProjectConfigured()) {
            throw new InvalidStateException('Cannot initialize already configured project');
        }
        $this->configureWebmasterEmail();
        $this->updateComposerJson();
        $this->updateBowerJson();
        $this->updatePackageJson();
    }

    private function configureWebmasterEmail()
    {
        $defaultNeonPath = $this->dir . '/app/config/default.neon';
        if (!file_exists($defaultNeonPath))
        {
            $this->errors[] = 'Webmaster e-mail not written into default.neon - no app/config/default.neon file found.';
            return;
        }
        $defaultNeon = file_get_contents($defaultNeonPath);
        $count = 0;
        file_put_contents($defaultNeonPath, preg_replace('~webmasterEmail: .*~',
            'webmasterEmail: ' . Neon::encode($this->webmasterEmail), $defaultNeon, -1, $count));
        if ($count === 0)
        {
            $this->errors[] = 'Webmaster e-mail not written into default.neon - string "webmasterEmail: " to be replaced not found.';
        }
    }

    private function updateBowerJson()
    {
        $bowerJsonFilePath = $this->dir . '/frontend/bower.json';
        $bowerJson = file_get_contents($bowerJsonFilePath);
        $bowerJsonConfig = Json::decode($bowerJson, Json::FORCE_ARRAY);
        $bowerJsonConfig['name'] = $_POST['project_name'];
        $bowerJsonConfig['description'] = $_POST['project_description'];
        $bowerJsonConfig['license'] = $_POST['project_license'];
        $bowerJsonConfig['version'] = $_POST['project_version'];
        $bowerJsonConfig['authors'] = [
            $_POST['author_name'],
        ];
        $bowerJson = Json::encode($bowerJsonConfig, Json::PRETTY);
        if (file_put_contents($bowerJsonFilePath, $bowerJson) === FALSE) {
            $this->errors[] = 'Bower.json config could not be written at [' . $bowerJsonFilePath . ']';
        }
    }

    private function updatePackageJson() {
        $packageJsonFilePath = $this->dir . '/frontend/package.json';
        $packageJson = file_get_contents($packageJsonFilePath);
        $packageJsonConfig = Json::decode($packageJson, Json::FORCE_ARRAY);
        $packageJsonConfig['name'] = $_POST['project_name'];
        $packageJsonConfig['description'] = $_POST['project_description'];
        $packageJsonConfig['license'] = $_POST['project_license'];
        $packageJsonConfig['version'] = $_POST['project_version'];
        $packageJsonConfig['author'] = $_POST['author_name'];
        $packageJson = Json::encode($packageJsonConfig, Json::PRETTY);
        file_put_contents($packageJsonFilePath, $packageJson);
        if (file_put_contents($packageJsonFilePath, $packageJson) === FALSE) {
            $this->errors[] = 'Package.json config could not be written at [' . $packageJsonFilePath . ']';
        }
    }

    private function updateComposerJson()
    {
        $composerJsonFilePath = $this->dir . '/composer.json';
        $composerJson = file_get_contents($composerJsonFilePath);
        $composerJsonConfig = Json::decode($composerJson, Json::FORCE_ARRAY);
        $composerJsonConfig['name'] = $_POST['project_name'];
        $composerJsonConfig['description'] = $_POST['project_description'];
        $composerJsonConfig['license'] = $_POST['project_license'];
        $composerJsonConfig['version'] = $_POST['project_version'];
        $composerJsonConfig['authors'] = [
            [
                'name' => $_POST['author_name'],
                'email' => $_POST['author_email'],
            ],
        ];
        $composerJson = Json::encode($composerJsonConfig, Json::PRETTY);
        if (FALSE === file_put_contents($composerJsonFilePath, $composerJson)) {
            $this->errors[] = 'Composer.json config could not be written at [' . $composerJsonFilePath . ']';
        }
    }

    public function initializeFromConsole()
    {
        $stdin = fopen("php://stdin","r");

        echo "Webmaster's e-mail > ";
        $this->webmasterEmail = trim(fgets($stdin));

        echo "Project's composer / bower / node package name (vendor/project) > ";
        $this->projectName = trim(fgets($stdin));

        echo "Project's description > ";
        $this->projectDescription = trim(fgets($stdin));

        echo "Project's license (i.e. proprietary) > ";
        $this->projectLicense = trim(fgets($stdin));

        echo "Project's initial version (i.e. 0.1.0) > ";
        $this->projectVersion = trim(fgets($stdin));

        echo "Author's name > ";
        $this->authorName = trim(fgets($stdin));

        echo "Author's e-mail > ";
        $this->authorEmail = trim(fgets($stdin));

        $this->initialize();

        foreach ($this->errors as $error) {
            echo $error . "\n";
        }

    }
}
