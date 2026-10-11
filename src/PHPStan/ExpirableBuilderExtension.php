<?php

namespace ALajusticia\Expirable\PHPStan;

use ALajusticia\Expirable\Traits\Expirable;
use Illuminate\Database\Eloquent\Builder;
use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

/**
 * Describe local macros without registering them on unrelated builders.
 */
class ExpirableBuilderExtension implements MethodsClassReflectionExtension
{
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (! $classReflection->is(Builder::class)) {
            return false;
        }

        $modelType = $this->getModelType($classReflection);

        if ($modelType === null) {
            return false;
        }

        $models = $modelType->getObjectClassReflections();

        if ($models === []) {
            return false;
        }

        foreach ($models as $model) {
            if (! $model->hasTraitUse(Expirable::class)) {
                return false;
            }
        }

        return (new GenericObjectType(ExpirableBuilderMethods::class, [$modelType]))
            ->hasMethod($methodName)->yes();
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        $modelType = $this->getModelType($classReflection);

        if ($modelType === null) {
            throw new \LogicException('An expirable builder must have a model type.');
        }

        $method = (new GenericObjectType(ExpirableBuilderMethods::class, [$modelType]))
            ->getMethod($methodName, new OutOfClassScope());
        $variant = $method->getVariants()[0];
        $returnType = $variant->getReturnType();

        if (in_array($methodName, ['withExpired', 'onlyExpired', 'onlyEternal', 'expiredSince'], true)) {
            $returnType = $classReflection->isGeneric()
                ? new GenericObjectType($classReflection->getName(), array_values($classReflection->getActiveTemplateTypeMap()->getTypes()))
                : new ObjectType($classReflection->getName());
        }

        return new ExpirableBuilderMethodReflection(
            $method,
            $classReflection,
            $returnType,
        );
    }

    private function getModelType(ClassReflection $classReflection): ?Type
    {
        do {
            $modelType = $classReflection->getActiveTemplateTypeMap()->getType('TModel');

            if ($modelType !== null) {
                return $modelType;
            }

            $classReflection = $classReflection->getParentClass();
        } while ($classReflection !== null);

        return null;
    }
}
