<?php

namespace App\Entity;

use App\Repository\RelationshipRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="user_id", orphanRemoval=true)
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity=Relationship::class, mappedBy="follower")
     */
    private $following;

    /**
     * @ORM\OneToMany(targetEntity=Relationship::class, mappedBy="followed")
     */
    private $followed_by;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->following = new ArrayCollection();
        $this->followed_by = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->username;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setUserId($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getUserId() === $this) {
                $post->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Relationship[]
     */
    public function getFollowing(): Collection
    {
        return $this->following;
    }

    public function addFollowing(Relationship $following): self
    {
        if (!$this->following->contains($following)) {
            $this->following[] = $following;
            $following->setFollower($this);
        }

        return $this;
    }

    public function removeFollowing(Relationship $following): self
    {
        if ($this->following->removeElement($following)) {
            // set the owning side to null (unless already changed)
            if ($following->getFollower() === $this) {
                $following->setFollower(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Relationship[]
     */
    public function getFollowedBy(): Collection
    {
        return $this->followed_by;
    }

    public function addFollowedBy(Relationship $followedBy): self
    {
        if (!$this->followed_by->contains($followedBy)) {
            $this->followed_by[] = $followedBy;
            $followedBy->setFollowed($this);
        }

        return $this;
    }

    public function removeFollowedBy(Relationship $followedBy): self
    {
        if ($this->followed_by->removeElement($followedBy)) {
            // set the owning side to null (unless already changed)
            if ($followedBy->getFollowed() === $this) {
                $followedBy->setFollowed(null);
            }
        }

        return $this;
    }
}
