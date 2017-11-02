<?php

namespace Ydzy\FileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * images
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ydzy\apiBundle\Entity\imagesRepository")
 */
class images
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="md5", type="string", length=255)
     */
    private $md5;
    /**
     * @var string
     *
     * @ORM\Column(name="original_image", type="string", length=1024)
     */
    private $originalImage;
    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=1024)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="thumbnail", type="string", length=1024)
     */
    private $thumbnail;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetime")
     */
    private $createDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set originalImage
     *
     * @param string $originalImage
     * @return images
     */
    public function setOriginalImage($originalImage)
    {
        $this->originalImage = $originalImage;

        return $this;
    }

    /**
     * Get originalImage
     *
     * @return string 
     */
    public function getOriginalImage()
    {
        return $this->originalImage;
    }

    /**
     * Set md5
     *
     * @param string $md5
     * @return images
     */
    public function setMd5($md5)
    {
        $this->md5 = $md5;

        return $this;
    }

    /**
     * Get md5
     *
     * @return string 
     */
    public function getMd5()
    {
        return $this->md5;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return images
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set thumbnail
     *
     * @param string $thumbnail
     * @return images
     */
    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    /**
     * Get thumbnail
     *
     * @return string 
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     * @return images
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate
     *
     * @return \DateTime 
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set updateDate
     *
     * @param \DateTime $updateDate
     * @return images
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     * Get updateDate
     *
     * @return \DateTime 
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return images
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }
}
