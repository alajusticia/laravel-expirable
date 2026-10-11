<?php

namespace ALajusticia\Expirable\PHPStan;

use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

/** @internal */
class ExpirableBuilderMethodReflection implements MethodReflection
{
    public function __construct(
        private MethodReflection $method,
        private ClassReflection $declaringClass,
        private Type $returnType,
    ) {
    }

    public function getName(): string
    {
        return $this->method->getName();
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->declaringClass;
    }

    public function getPrototype(): ClassMemberReflection
    {
        return $this;
    }

    public function isStatic(): bool
    {
        return false;
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    /** @return list<ParametersAcceptor> */
    public function getVariants(): array
    {
        $variant = $this->method->getVariants()[0];

        return [new FunctionVariant(
            $variant->getTemplateTypeMap(),
            $variant->getResolvedTemplateTypeMap(),
            $variant->getParameters(),
            $variant->isVariadic(),
            $this->returnType,
        )];
    }

    public function getDocComment(): ?string
    {
        return $this->method->getDocComment();
    }

    public function isDeprecated(): TrinaryLogic
    {
        return $this->method->isDeprecated();
    }

    public function getDeprecatedDescription(): ?string
    {
        return $this->method->getDeprecatedDescription();
    }

    public function isFinal(): TrinaryLogic
    {
        return $this->method->isFinal();
    }

    public function isInternal(): TrinaryLogic
    {
        return $this->method->isInternal();
    }

    public function getThrowType(): ?Type
    {
        return $this->method->getThrowType();
    }

    public function hasSideEffects(): TrinaryLogic
    {
        return TrinaryLogic::createYes();
    }
}
