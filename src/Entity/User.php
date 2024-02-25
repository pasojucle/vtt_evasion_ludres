<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Licence;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const APPROVAL_RIGHT_TO_THE_IMAGE = 1;

    public const APPROVAL_GOING_HOME_ALONE = 2;

    public const APPROVALS = [
        self::APPROVAL_RIGHT_TO_THE_IMAGE => 'approval.right_to_the_image',
        self::APPROVAL_GOING_HOME_ALONE => 'approval.going_home_alone',
    ];

    public const PERMISSION_BIKE_RIDE = 'BIKE_RIDE';
    public const PERMISSION_USER = 'USER';
    public const PERMISSION_PRODUCT = 'PRODUCT';
    public const PERMISSION_SURVEY = 'SURVEY';
    public const PERMISSION_MODAL_WINDOW = 'MODAL_WINDOW';
    public const PERMISSION_SECOND_HAND = 'SECOND_HAND';
    public const PERMISSION_PERMISSION = 'PERMISSION';
    public const PERMISSION_DOCUMENTATION = 'DOCUMENTATION';
    public const PERMISSION_SLIDESHOW = 'SLIDESHOW';
    public const PERMISSIONS = [
        self::PERMISSION_BIKE_RIDE => 'permission.bike_ride',
        self::PERMISSION_USER => 'permission.user',
        self::PERMISSION_PRODUCT => 'permission.product',
        self::PERMISSION_SURVEY => 'permission.survey',
        self::PERMISSION_MODAL_WINDOW => 'permission.modal_window',
        self::PERMISSION_SECOND_HAND => 'permission.second_hand',
        self::PERMISSION_DOCUMENTATION => 'permission.documentation',
        self::PERMISSION_SLIDESHOW => 'permission.slideshow',
    ];

    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[Column(type: 'string', length: 25, unique: true)]
    private string $licenceNumber = '';

    #[Column(type: 'json')]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[Column(type: 'string')]
    private string $password;

    #[Column(type: 'boolean')]
    private bool $active = true;

    /**
     * @var ArrayCollection <Identity>
     */
    #[OneToMany(targetEntity: Identity::class, mappedBy: 'user')]
    private Collection $identities;

    #[OneToOne(targetEntity: Health::class, inversedBy: 'user', cascade: ['persist', 'remove'])]

    // #[OneToOne(targetEntity: Health::class, cascade: ['persist', 'remove'])]
    private ?Health $health;

    #[OneToMany(targetEntity: Approval::class, mappedBy: 'user')]
    private $approvals;

    #[OneToMany(targetEntity: Licence::class, mappedBy: 'user')]
    private Collection $licences;

    /**
     * @var ArrayCollection <Session>
     */
    #[OneToMany(targetEntity: Session::class, mappedBy: 'user')]
    private Collection $sessions;

    #[ManyToOne(targetEntity: Level::class, inversedBy: 'users')]
    private ?Level $level = null;

    #[Column(type: 'boolean', options:['default' => false])]
    private $passwordMustBeChanged = false;

    #[OneToMany(targetEntity: OrderHeader::class, mappedBy: 'user')]
    private Collection $orderHeaders;

    #[OneToMany(mappedBy: 'user', targetEntity: Respondent::class)]
    private Collection $respondents;

    #[ManyToMany(targetEntity: Survey::class, mappedBy: 'members')]
    private Collection $surveys;

    #[Column(type: 'boolean', options:['default' => false])]
    private bool $loginSend = false;

    #[Column(type: 'boolean', options:['default' => false])]
    private $protected = false;

    #[ManyToMany(targetEntity: BikeRide::class, mappedBy: 'users', cascade: ['persist', 'remove'], fetch: 'EAGER', orphanRemoval: true)]
    private Collection $bikeRides;

    #[ManyToOne(inversedBy: 'users')]
    private ?BoardRole $boardRole = null;

    #[OneToMany(mappedBy: 'user', targetEntity: RegistrationChange::class)]
    private Collection $registrationChanges;

    #[OneToMany(mappedBy: 'user', targetEntity: SecondHand::class)]
    #[OrderBy(['createdAt' => 'DESC'])]
    private Collection $secondHands;

    #[Column(type: 'json', nullable: true)]
    private ?array $permissions = null;

    public function __construct()
    {
        $this->identities = new ArrayCollection();
        $this->approvals = new ArrayCollection();
        $this->licences = new ArrayCollection();
        $this->sessions = new ArrayCollection();
        $this->orderHeaders = new ArrayCollection();
        $this->health = null;
        $this->respondents = new ArrayCollection();
        $this->surveys = new ArrayCollection();
        $this->bikeRides = new ArrayCollection();
        $this->registrationChanges = new ArrayCollection();
        $this->secondHands = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLicenceNumber(): ?string
    {
        return $this->licenceNumber;
    }

    public function setLicenceNumber(string $licenceNumber): self
    {
        $this->licenceNumber = $licenceNumber;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->licenceNumber;
    }

    /**
     * The public representation of the user (e.g. a username, an email address, etc.).
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->licenceNumber;
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

    public function addRole(string $role): self
    {
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(string $role): self
    {
        $key = array_search($role, $this->roles, true);

        if (false !== $key) {
            unset($this->roles[$key]);
        }

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
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection|Identity[]
     */
    public function getIdentities(): Collection
    {
        return $this->identities;
    }

    public function addIdentity(Identity $identity): self
    {
        if (!$this->identities->contains($identity)) {
            $this->identities[] = $identity;
            $identity->setUser($this);
        }

        return $this;
    }

    public function removeIdentity(Identity $identity): self
    {
        if ($this->identities->removeElement($identity)) {
            // set the owning side to null (unless already changed)
            if ($identity->getUser() === $this) {
                $identity->setUser(null);
            }
        }

        return $this;
    }

    public function getFirstIdentity(): ?Identity
    {
        if (!$this->identities->isEmpty()) {
            return $this->identities->first();
        }

        return null;
    }

    public function getMemberIdentity(): Identity|false
    {
        $criteria = Criteria::create()
            ->andWhere(
                Criteria::expr()->eq('type', Identity::TYPE_MEMBER),
            )
        ;

        return $this->identities->matching($criteria)->first();
    }

    public function getKinshipIdentity(): Identity|false
    {
        $criteria = Criteria::create()
            ->andWhere(
                Criteria::expr()->eq('type', Identity::TYPE_KINSHIP),
            )
        ;

        return $this->identities->matching($criteria)->first();
    }
    

    public function getHealth(): ?Health
    {
        return $this->health;
    }

    public function setHealth(?Health $health): self
    {
        $this->health = $health;

        return $this;
    }

    /**
     * @return Approval[]|Collection
     */
    public function getApprovals(): Collection
    {
        return $this->approvals;
    }

    public function addApproval(Approval $approval): self
    {
        if (!$this->approvals->contains($approval)) {
            $this->approvals[] = $approval;
            $approval->setUser($this);
        }

        return $this;
    }

    public function removeApproval(Approval $approval): self
    {
        if ($this->approvals->removeElement($approval)) {
            // set the owning side to null (unless already changed)
            if ($approval->getUser() === $this) {
                $approval->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Licence[]
     */
    public function getLicences(): Collection
    {
        return $this->licences;
    }

    public function addLicence(Licence $licence): self
    {
        if (!$this->licences->contains($licence)) {
            $this->licences[] = $licence;
            $licence->setUser($this);
        }

        return $this;
    }

    public function removeLicence(Licence $licence): self
    {
        if ($this->licences->removeElement($licence)) {
            // set the owning side to null (unless already changed)
            if ($licence->getUser() === $this) {
                $licence->setUser(null);
            }
        }

        return $this;
    }

    public function getSeasonLicence(int $season): ?Licence
    {
        foreach ($this->licences as $licence) {
            if ($season === $licence->getSeason()) {
                return $licence;
            }
        }

        return null;
    }

    public function getLastLicence(): ?licence
    {
        $lastSeason = 1900;
        $lastLicence = null;
        foreach ($this->licences as $licence) {
            if ($licence->getSeason() > $lastSeason) {
                $lastSeason = $licence->getSeason();
                $lastLicence = $licence;
            }
        }

        return $lastLicence;
    }

    /**
     * @return Collection|Session[]
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): self
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions[] = $session;
            $session->setUser($this);
        }

        return $this;
    }

    public function removeSession(Session $session): self
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getUser() === $this) {
                $session->setUser(null);
            }
        }

        return $this;
    }

    public function getDoneSessions(): Collection
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('isPresent', true))
        ;

        return $this->sessions->matching($criteria);
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function isPasswordMustBeChanged(): ?bool
    {
        return $this->passwordMustBeChanged;
    }

    public function setPasswordMustBeChanged(bool $passwordMustBeChanged): self
    {
        $this->passwordMustBeChanged = $passwordMustBeChanged;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->getFirstIdentity()->getName() . ' ' . $this->getFirstIdentity()->getfirstName();
    }

    /**
     * @return Collection|OrderHeader[]
     */
    public function getOrderHeaders(): Collection
    {
        return $this->orderHeaders;
    }

    public function addOrderHeader(OrderHeader $orderHeader): self
    {
        if (!$this->orderHeaders->contains($orderHeader)) {
            $this->orderHeaders[] = $orderHeader;
            $orderHeader->setUser($this);
        }

        return $this;
    }

    public function removeOrderHeader(OrderHeader $orderHeader): self
    {
        if ($this->orderHeaders->removeElement($orderHeader)) {
            // set the owning side to null (unless already changed)
            if ($orderHeader->getUser() === $this) {
                $orderHeader->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Respondent>
     */
    public function getRespondents(): Collection
    {
        return $this->respondents;
    }

    public function addRespondent(Respondent $respondent): self
    {
        if (!$this->respondents->contains($respondent)) {
            $this->respondents[] = $respondent;
            $respondent->setUser($this);
        }

        return $this;
    }

    public function removeRespondent(Respondent $respondent): self
    {
        if ($this->respondents->removeElement($respondent)) {
            // set the owning side to null (unless already changed)
            if ($respondent->getUser() === $this) {
                $respondent->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Survey>
     */
    public function getSurveys(): Collection
    {
        return $this->surveys;
    }

    public function addSurvey(Survey $survey): self
    {
        if (!$this->surveys->contains($survey)) {
            $this->surveys[] = $survey;
            $survey->addMember($this);
        }

        return $this;
    }

    public function removeSurvey(Survey $survey): self
    {
        if ($this->surveys->removeElement($survey)) {
            $survey->removeMember($this);
        }

        return $this;
    }

    public function isLoginSend(): ?bool
    {
        return $this->loginSend;
    }

    public function setLoginSend(bool $loginSend): self
    {
        $this->loginSend = $loginSend;

        return $this;
    }

    public function isProtected(): ?bool
    {
        return $this->protected;
    }

    public function setProtected(bool $protected): self
    {
        $this->protected = $protected;

        return $this;
    }

    /**
     * @return Collection|BikeRide[]
     */
    public function getBikeRides(): Collection
    {
        return $this->bikeRides;
    }

    public function addBikeRide(BikeRide $bikeRide): self
    {
        if (!$this->bikeRides->contains($bikeRide)) {
            $this->bikeRides[] = $bikeRide;
            $bikeRide->addUser($this);
        }

        return $this;
    }

    public function removeBikeRide(BikeRide $bikeRide): self
    {
        if ($this->bikeRides->removeElement($bikeRide)) {
            $bikeRide->removeUser($this);
        }

        return $this;
    }

    public function getBoardRole(): ?BoardRole
    {
        return $this->boardRole;
    }

    public function setBoardRole(?BoardRole $boardRole): self
    {
        $this->boardRole = $boardRole;

        return $this;
    }

    /**
     * @return Collection<int, RegistrationChange>
     */
    public function getRegistrationChanges(): Collection
    {
        return $this->registrationChanges;
    }

    public function addRegistrationChange(RegistrationChange $registrationChange): static
    {
        if (!$this->registrationChanges->contains($registrationChange)) {
            $this->registrationChanges->add($registrationChange);
            $registrationChange->setUser($this);
        }

        return $this;
    }

    public function removeRegistrationChange(RegistrationChange $registrationChange): static
    {
        if ($this->registrationChanges->removeElement($registrationChange)) {
            // set the owning side to null (unless already changed)
            if ($registrationChange->getUser() === $this) {
                $registrationChange->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SecondHand>
     */
    public function getSecondHands(): Collection
    {
        return $this->secondHands;
    }

    public function addSecondHand(SecondHand $secondHand): static
    {
        if (!$this->secondHands->contains($secondHand)) {
            $this->secondHands->add($secondHand);
            $secondHand->setUser($this);
        }

        return $this;
    }

    public function removeSecondHand(SecondHand $secondHand): static
    {
        if ($this->secondHands->removeElement($secondHand)) {
            // set the owning side to null (unless already changed)
            if ($secondHand->getUser() === $this) {
                $secondHand->setUser(null);
            }
        }

        return $this;
    }

    public function getPermissions(): array
    {
        $permissions = [
            self::PERMISSION_BIKE_RIDE => false,
            self::PERMISSION_USER => false,
            self::PERMISSION_PRODUCT => false,
            self::PERMISSION_SURVEY => false,
            self::PERMISSION_MODAL_WINDOW => false,
            self::PERMISSION_SECOND_HAND => false,
        ];
        return ($this->permissions) ? array_merge($permissions, $this->permissions) : $permissions;
    }

    public function hasPermissions(string|array $names): bool
    {
        if (null === $this->permissions) {
            return false;
        }
        if (is_string($names)) {
            $names = [$names];
        }
        foreach ($names as $name) {
            if (array_key_exists($name, $this->permissions) && true === $this->permissions[$name]) {
                return true;
            }
        }
        return false;
    }

    public function hasAtLeastOnePermission(): bool
    {
        if ($this->permissions) {
            foreach ($this->permissions as $permission) {
                if (true === $permission) {
                    return true;
                }
            }
        }

        return false;
    }

    public function setPermissions(?array $permissions): static
    {
        $this->permissions = $permissions;

        return $this;
    }
}
