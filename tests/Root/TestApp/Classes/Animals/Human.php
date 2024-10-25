<?php

namespace YonisSavary\Sharp\Tests\Root\TestApp\Classes\Animals;


class Human extends AbstractAnimal implements SwimingAnimalInterface, WalkingAnimalInterface
{
    use CanTalk;
}