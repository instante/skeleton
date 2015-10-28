<?php
namespace Instante\Deployment;


use Nette\InvalidStateException;
use Nette\Neon\Neon;

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

    public function checkProjectInitialized()
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
        if ($this->checkProjectInitialized()) {
            throw new InvalidStateException('Cannot initialize already initialized project');
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

        file_put_contents($defaultNeonPath, str_replace('webmasterEmail: john.doe@example.com',
            'webmasterEmail: ' . Neon::encode($this->webmasterEmail), $defaultNeon, $count));
        if ($count === 0)
        {
            $this->errors[] = 'Webmaster e-mail not written into default.neon - string "webmasterEmail: john.doe@example.com" to be replaced not found.';
        }
    }
}
