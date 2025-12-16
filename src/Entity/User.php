<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\GardianKindEnum;
use App\Entity\Enum\IdentityKindEnum;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\PermissionEnum;
use App\Entity\Licence;
use App\Entity\UserPermission;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const APPROVAL_RIGHT_TO_THE_IMAGE = 1;

    public const APPROVAL_GOING_HOME_ALONE = 2;

    public const APPROVAL_EMERGENCY_CARE = 3;

    public const APPROVALS = [
        self::APPROVAL_RIGHT_TO_THE_IMAGE => 'approval.right_to_the_image',
        self::APPROVAL_GOING_HOME_ALONE => 'approval.going_home_alone',
        self::APPROVAL_EMERGENCY_CARE => 'approval.emergency_care',
    ];

    public const PERMISSION_BIKE_RIDE_CLUSTER = 'BIKE_RIDE_CLUSTER';
    public const PERMISSION_BIKE_RIDE = 'BIKE_RIDE';
    public const PERMISSION_USER = 'USER';
    public const PERMISSION_PRODUCT = 'PRODUCT';
    public const PERMISSION_SURVEY = 'SURVEY';
    public const PERMISSION_MODAL_WINDOW = 'MODAL_WINDOW';
    public const PERMISSION_SECOND_HAND = 'SECOND_HAND';
    public const PERMISSION_PERMISSION = 'PERMISSION';
    public const PERMISSION_DOCUMENTATION = 'DOCUMENTATION';
    public const PERMISSION_SLIDESHOW = 'SLIDESHOW';
    public const PERMISSION_PARTICIPATION = 'PARTICIPATION';
    public const PERMISSION_SUMMARY = 'SUMMARY';
    public const PERMISSIONS = [
        self::PERMISSION_BIKE_RIDE_CLUSTER => 'permission.bike_ride_cluster',
        self::PERMISSION_BIKE_RIDE => 'permission.bike_ride',
        self::PERMISSION_USER => 'permission.user',
        self::PERMISSION_PRODUCT => 'permission.product',
        self::PERMISSION_SURVEY => 'permission.survey',
        self::PERMISSION_MODAL_WINDOW => 'permission.notification',
        self::PERMISSION_SECOND_HAND => 'permission.second_hand',
        self::PERMISSION_DOCUMENTATION => 'permission.documentation',
        self::PERMISSION_SLIDESHOW => 'permission.slideshow',
        self::PERMISSION_PARTICIPATION => 'permission.participation',
        self::PERMISSION_SUMMARY => 'permission.summary',
    ];

    #[ORM\Column(type: 'integer')]
    #[ORM\Id, ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 25, unique: true)]
    private string $licenceNumber = '';

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    #[ORM\OneToMany(targetEntity: Licence::class, mappedBy: 'user')]
    private Collection $licences;

    /**
     * @var ArrayCollection <Session>
     */
    #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'user')]
    private Collection $sessions;

    #[ORM\ManyToOne(targetEntity: Level::class, inversedBy: 'users')]
    private ?Level $level = null;

    #[ORM\Column(type: 'boolean', options:['default' => false])]
    private $passwordMustBeChanged = false;

    #[ORM\OneToMany(targetEntity: OrderHeader::class, mappedBy: 'user')]
    private Collection $orderHeaders;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Respondent::class)]
    private Collection $respondents;

    #[ORM\ManyToMany(targetEntity: Survey::class, mappedBy: 'members')]
    private Collection $surveys;

    #[ORM\Column(type: 'boolean', options:['default' => false])]
    private bool $loginSend = false;

    #[ORM\Column(type: 'boolean', options:['default' => false])]
    private $protected = false;

    #[ORM\ManyToMany(targetEntity: BikeRide::class, mappedBy: 'users', cascade: ['persist', 'remove'], fetch: 'EAGER', orphanRemoval: true)]
    private Collection $bikeRides;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?BoardRole $boardRole = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: History::class)]
    private Collection $registrationChanges;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: SecondHand::class)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $secondHands;

    /**
     * @var Collection<int, UserSkill>
     */
    #[ORM\OneToMany(targetEntity: UserSkill::class, mappedBy: 'user')]
    private Collection $userSkills;

    /**
     * @var Collection<int, UserPermission>
     */
    #[ORM\OneToMany(targetEntity: UserPermission::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $userPermissions;

    private array $permissions = [];

    /**
     * @var ArrayCollection<UserGardian>
     */
    #[ORM\OneToMany(targetEntity: UserGardian::class, mappedBy: 'user')]
    private Collection $userGardians;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Health $health = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Identity $identity = null;

    public function __construct()
    {
        $this->licences = new ArrayCollection();
        $this->sessions = new ArrayCollection();
        $this->orderHeaders = new ArrayCollection();
        $this->respondents = new ArrayCollection();
        $this->surveys = new ArrayCollection();
        $this->bikeRides = new ArrayCollection();
        $this->registrationChanges = new ArrayCollection();
        $this->secondHands = new ArrayCollection();
        $this->userSkills = new ArrayCollection();
        $this->userPermissions = new ArrayCollection();
        $this->permissions = [];
        $this->userGardians = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getIdentity()->getName() . ' ' . $this->getIdentity()->getFirstName();
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
     * @return Collection<int, History>
     */
    public function getRegistrationChanges(): Collection
    {
        return $this->registrationChanges;
    }

    public function addRegistrationChange(History $history): static
    {
        if (!$this->registrationChanges->contains($history)) {
            $this->registrationChanges->add($history);
            $history->setUser($this);
        }

        return $this;
    }

    public function removeRegistrationChange(History $history): static
    {
        if ($this->registrationChanges->removeElement($history)) {
            // set the owning side to null (unless already changed)
            if ($history->getUser() === $this) {
                $history->setUser(null);
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

    /**
     * @return Collection<int, UserSkill>
     */
    public function getUserSkills(): Collection
    {
        return $this->userSkills;
    }

    public function addUserSkill(UserSkill $userSkill): static
    {
        if (!$this->userSkills->contains($userSkill)) {
            $this->userSkills->add($userSkill);
            $userSkill->setUser($this);
        }

        return $this;
    }

    public function removeUserSkill(UserSkill $userSkill): static
    {
        if ($this->userSkills->removeElement($userSkill)) {
            // set the owning side to null (unless already changed)
            if ($userSkill->getUser() === $this) {
                $userSkill->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserPermission>
     */
    public function getUserPermissions(): Collection
    {
        return $this->userPermissions;
    }

    /**
     * @return array<int, PermissionEnum>
     */
    public function getPermissions(): array
    {
        /** @var UserPermission $userPermission */
        foreach ($this->userPermissions as $userPermission) {
            $this->permissions[] = $userPermission->getPermission();
        }
    
        return $this->permissions;
    }

    public function addPermission(PermissionEnum $permission): static
    {
        if (!array_search($permission, $this->permissions)) {
            $userPermission = new UserPermission();
            $this->userPermissions->add($userPermission);
            $userPermission->setUser($this)
                ->setPermission($permission);
        }

        return $this;
    }

    public function removePermission(PermissionEnum $permission): static
    {
        /** @var UserPermission $userPermission */
        foreach ($this->userPermissions as $userPermission) {
            if ($userPermission->getPermission() === $permission) {
                $this->userPermissions->removeElement($userPermission);
            }
        }

        return $this;
    }

    public function hasPermissions(PermissionEnum|array $permissions): bool
    {
        if (empty($this->permissions)) {
            return false;
        }

        if ($permissions instanceof PermissionEnum) {
            $permissions = [$permissions];
        }

        foreach ($permissions as $permission) {
            if (false !== array_search($permission, $this->permissions)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Collection<int, UserGardian>
     */
    public function getUserGardians(): Collection
    {
        return $this->userGardians;
    }

    public function getLegalGardian(): ?UserGardian
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('kind', GardianKindEnum::LEGAL_GARDIAN))
        ;

        return $this->userGardians->matching($criteria)->first() ?: null;
    }

    public function getSecondContact(): ?UserGardian
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('kind', GardianKindEnum::SECOND_CONTACT))
        ;

        return $this->userGardians->matching($criteria)->first() ?: null;
    }


    public function addUserGardian(UserGardian $userGardian): static
    {
        if (!$this->userGardians->contains($userGardian)) {
            $this->userGardians->add($userGardian);
            $userGardian->setUser($this);
        }

        return $this;
    }

    public function removeUserGardian(UserGardian $userGardian): static
    {
        if ($this->userGardians->removeElement($userGardian)) {
            // set the owning side to null (unless already changed)
            if ($userGardian->getUser() === $this) {
                $userGardian->setUser(null);
            }
        }

        return $this;
    }

    public function getHealth(): ?Health
    {
        return $this->health;
    }

    public function setHealth(?Health $health): static
    {
        // unset the owning side of the relation if necessary
        if ($health === null && $this->health !== null) {
            $this->health->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($health !== null && $health->getUser() !== $this) {
            $health->setUser($this);
        }

        $this->health = $health;

        return $this;
    }

    public function getMainIdentity(): ?Identity
    {
        $identity = (LicenceCategoryEnum::SCHOOL === $this->getLastLicence()->getCategory())
                ? $this->getLegalGardian()->getIdentity()
                : $this->getIdentity();

        return $identity;
    }

    public function getIdentity(): ?Identity
    {
        return $this->identity;
    }

    public function setIdentity(?Identity $identity): static
    {
        // unset the owning side of the relation if necessary
        if ($identity === null && $this->identity !== null) {
            $this->identity->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($identity !== null && $identity->getUser() !== $this) {
            $identity->setUser($this);
        }

        $this->identity = $identity;

        return $this;
    }
}
