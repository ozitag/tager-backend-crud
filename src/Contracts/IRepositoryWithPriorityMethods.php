<?php

namespace OZiTAG\Tager\Backend\Crud\Contracts;

interface IRepositoryWithPriorityMethods
{
    public function findItemWithMaxPriority($conditionalAttributes = []);

    public function findFirstWithLowerPriorityThan($priority, $conditionalAttributes = []);

    public function findFirstWithHigherPriorityThan($priority, $conditionalAttributes = []);
}
