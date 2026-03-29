<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\GardianKindEnum;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\PermissionEnum;
use App\Entity\MemberGardian;
use App\Entity\MemberPermission;
use App\Entity\MemberSkill;
use App\Repository\MemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
class Member extends User implements PasswordAuthenticatedUserInterface
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

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: 'string', nullable:true)]
    private ?string $password;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    #[ORM\Column(type: 'boolean', options:['default' => false])]
    private $passwordMustBeChanged = false;

    #[ORM\OneToMany(targetEntity: OrderHeader::class, mappedBy: 'member')]
    private Collection $orderHeaders;

    #[ORM\OneToMany(mappedBy: 'member', targetEntity: Respondent::class)]
    private Collection $respondents;

    #[ORM\ManyToMany(targetEntity: Survey::class, mappedBy: 'members')]
    private Collection $surveys;

    #[ORM\Column(type: 'boolean', options:['default' => false])]
    private bool $loginSend = false;

    #[ORM\Column(type: 'boolean', options:['default' => false])]
    private $protected = false;

    #[ORM\ManyToMany(targetEntity: BikeRide::class, mappedBy: 'members', cascade: ['persist', 'remove'], fetch: 'EAGER', orphanRemoval: true)]
    private Collection $bikeRides;

    #[ORM\ManyToOne(inversedBy: 'members')]
    private ?BoardRole $boardRole = null;

    #[ORM\OneToMany(mappedBy: 'member', targetEntity: History::class)]
    private Collection $registrationChanges;

    #[ORM\OneToMany(mappedBy: 'member', targetEntity: SecondHand::class)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $secondHands;

    /**
     * @var Collection<int, MemberSkill>
     */
    #[ORM\OneToMany(targetEntity: MemberSkill::class, mappedBy: 'member')]
    private Collection $memberSkills;

    /**
     * @var Collection<int, MemberPermission>
     */
    #[ORM\OneToMany(targetEntity: MemberPermission::class, mappedBy: 'member', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $memberPermissions;

    private array $permissions = [];

    /**
     * @var ArrayCollection<MemberGardian>
     */
    #[ORM\OneToMany(targetEntity: MemberGardian::class, mappedBy: 'member')]
    private Collection $memberGardians;

    #[ORM\OneToOne(mappedBy: 'member', cascade: ['persist', 'remove'])]
    private ?Health $health = null;

    public function __construct()
    {
        parent::__construct();
        $this->bikeRides = new ArrayCollection();
        $this->orderHeaders = new ArrayCollection();
        $this->respondents = new ArrayCollection();
        $this->surveys = new ArrayCollection();
        $this->registrationChanges = new ArrayCollection();
        $this->secondHands = new ArrayCollection();
        $this->memberSkills = new ArrayCollection();
        $this->memberPermissions = new ArrayCollection();
        $this->permissions = [];
        $this->memberGardians = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getIdentity()->getName() . ' ' . $this->getIdentity()->getFirstName();
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
            $orderHeader->setMember($this);
        }

        return $this;
    }

    public function removeOrderHeader(OrderHeader $orderHeader): self
    {
        if ($this->orderHeaders->removeElement($orderHeader)) {
            // set the owning side to null (unless already changed)
            if ($orderHeader->getMember() === $this) {
                $orderHeader->setMember(null);
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
            $respondent->setMember($this);
        }

        return $this;
    }

    public function removeRespondent(Respondent $respondent): self
    {
        if ($this->respondents->removeElement($respondent)) {
            // set the owning side to null (unless already changed)
            if ($respondent->getMember() === $this) {
                $respondent->setMember(null);
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
            $bikeRide->addMember($this);
        }

        return $this;
    }

    public function removeBikeRide(BikeRide $bikeRide): self
    {
        if ($this->bikeRides->removeElement($bikeRide)) {
            $bikeRide->removeMember($this);
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
            $history->setMember($this);
        }

        return $this;
    }

    public function removeRegistrationChange(History $history): static
    {
        if ($this->registrationChanges->removeElement($history)) {
            // set the owning side to null (unless already changed)
            if ($history->getMember() === $this) {
                $history->setMember(null);
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
            $secondHand->setMember($this);
        }

        return $this;
    }

    public function removeSecondHand(SecondHand $secondHand): static
    {
        if ($this->secondHands->removeElement($secondHand)) {
            // set the owning side to null (unless already changed)
            if ($secondHand->getMember() === $this) {
                $secondHand->setMember(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MemberSkill>
     */
    public function getMemberSkills(): Collection
    {
        return $this->memberSkills;
    }

    public function addMemberSkill(MemberSkill $memberSkill): static
    {
        if (!$this->memberSkills->contains($memberSkill)) {
            $this->memberSkills->add($memberSkill);
            $memberSkill->setMember($this);
        }

        return $this;
    }

    public function removeMemberSkill(MemberSkill $memberSkill): static
    {
        if ($this->memberSkills->removeElement($memberSkill)) {
            // set the owning side to null (unless already changed)
            if ($memberSkill->getMember() === $this) {
                $memberSkill->setMember(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MemberPermission>
     */
    public function getMemberPermissions(): Collection
    {
        return $this->memberPermissions;
    }

    /**
     * @return array<int, PermissionEnum>
     */
    public function getPermissions(): array
    {
        /** @var MemberPermission $memberPermission */
        foreach ($this->memberPermissions as $memberPermission) {
            $this->permissions[] = $memberPermission->getPermission();
        }
    
        return $this->permissions;
    }

    public function addPermission(PermissionEnum $permission): static
    {
        if (!array_search($permission, $this->permissions)) {
            $memberPermission = new MemberPermission();
            $this->memberPermissions->add($memberPermission);
            $memberPermission->setMember($this)
                ->setPermission($permission);
        }

        return $this;
    }

    public function removePermission(PermissionEnum $permission): static
    {
        /** @var MemberPermission $memberPermission */
        foreach ($this->memberPermissions as $memberPermission) {
            if ($memberPermission->getPermission() === $permission) {
                $this->memberPermissions->removeElement($memberPermission);
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
     * @return Collection<int, MemberGardian>
     */
    public function getMemberGardians(): Collection
    {
        return $this->memberGardians;
    }

    public function getLegalGardian(): ?MemberGardian
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('kind', GardianKindEnum::LEGAL_GARDIAN))
        ;

        return $this->memberGardians->matching($criteria)->first() ?: null;
    }

    public function getSecondContact(): ?MemberGardian
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('kind', GardianKindEnum::SECOND_CONTACT))
        ;

        return $this->memberGardians->matching($criteria)->first() ?: null;
    }


    public function addMemberGardian(MemberGardian $memberGardian): static
    {
        if (!$this->memberGardians->contains($memberGardian)) {
            $this->memberGardians->add($memberGardian);
            $memberGardian->setMember($this);
        }

        return $this;
    }

    public function removeMemberGardian(MemberGardian $memberGardian): static
    {
        if ($this->memberGardians->removeElement($memberGardian)) {
            // set the owning side to null (unless already changed)
            if ($memberGardian->getMember() === $this) {
                $memberGardian->setMember(null);
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
            $this->health->setMember(null);
        }

        // set the owning side of the relation if necessary
        if ($health !== null && $health->getMember() !== $this) {
            $health->setMember($this);
        }

        $this->health = $health;

        return $this;
    }

    public function getMainIdentity(): ?Identity
    {
        $identity = (LicenceCategoryEnum::SCHOOL === $this->getLastLicence()?->getCategory())
                ? $this->getLegalGardian()?->getIdentity()
                : $this->getIdentity();

        return $identity;
    }

    public function getContactEmail(): ?string
    {
        return $this->getMainIdentity()?->getEmail();
    }
}
