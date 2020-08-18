<?php

namespace OZiTAG\Tager\Backend\Crud\Contracts;

interface IRepositoryWithPriorityMethods
{
    public function findFirstWithLowerPriorityThan($priority, $conditionalAttributes = []);

    public function findFirstWithHigherPriorityThan($priority, $conditionalAttributes = []);
}
