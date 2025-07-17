<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Entity;

use App\HealthTracker\Domain\Enum\Gender;
use App\HealthTracker\Domain\ValueObject\User\FullName;
use App\HealthTracker\Domain\ValueObject\User\TelegramUserId;
use App\HealthTracker\Domain\ValueObject\User\TelegramUsername;
use App\HealthTracker\Domain\ValueObject\User\UserId;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "users")]
#[ORM\UniqueConstraint(name: 'ux__users__telegram_user_id', columns: ['telegram_user_id'])]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'user_id')]
    private(set) ?UserId $id = null;

    #[ORM\Column(type: 'telegram_user_id')]
    private(set) TelegramUserId $telegramUserId;

    #[ORM\Column(type: 'telegram_username', nullable: true)]
    private(set) ?TelegramUsername $telegramUsername = null;

    #[ORM\Embedded(class: FullName::class, columnPrefix: false)]
    private(set) FullName $fullName;

    #[ORM\Column(name: 'gender', type: Types::SMALLINT, nullable: true, enumType: Gender::class)]
    public ?Gender $gender = null {
        get => $this->gender;
        set => $this->gender = $value;
    }

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    public ?DateTimeInterface $birthdate = null {
        get => $this->birthdate;
        set => $this->birthdate = $value;
    }

    #[ORM\OneToOne(targetEntity: UserIndicator::class, mappedBy: 'user', cascade: ['persist'], orphanRemoval: true)]
    public ?UserIndicator $indicator = null {
        get => $this->indicator;
        set => $this->indicator = $value;
    }

    #[ORM\OneToOne(targetEntity: UserDailyNorm::class, mappedBy: 'user', cascade: ['persist'], orphanRemoval: true)]
    public ?UserDailyNorm $dailyNorm = null {
        get => $this->dailyNorm;
        set => $this->dailyNorm = $value;
    }

    /**
     * @var Collection<int, WeightMeasurement>
     */
    #[ORM\OneToMany(targetEntity: WeightMeasurement::class, mappedBy: 'user', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private(set) Collection $weightMeasurements;

    public function __construct(
        TelegramUserId $telegramUserId,
        ?TelegramUsername $telegramUsername,
        FullName $fullName,
    )
    {
        $this->telegramUserId = $telegramUserId;
        $this->telegramUsername = $telegramUsername;
        $this->fullName = $fullName;

        $this->weightMeasurements = new ArrayCollection();
    }

    public function getAge(): ?int
    {
        if ($this->birthdate === null) {
            return null;
        }

        return new DateTimeImmutable()->diff($this->birthdate)->y;
    }

    public function isFilled(bool $validateDailyNorm = true): bool
    {
        return $this->hasIndicator()
            && (!$validateDailyNorm || $this->hasDailyNorm())
            && $this->indicator->isFilled()
            && $this->gender !== null
            && $this->birthdate !== null;
    }

    public function hasIndicator(): bool
    {
        return $this->indicator !== null;
    }

    public function hasDailyNorm(): bool
    {
        return $this->dailyNorm !== null;
    }
}
