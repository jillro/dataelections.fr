<?php

/*
 * Copyright 2015 Guillaume Royer
 *
 * This file is part of DataElections.
 *
 * DataElections is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * DataElections is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with DataElections. If not, see <http://www.gnu.org/licenses/>.
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EcheanceChoiceType extends AbstractType
{
    private $allEcheances;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        return $builder
            ->add('echeances', 'entity', array(
                'class' => 'AppBundle\Domain\Election\Entity\Echeance\Echeance',
                'choices' => $this->allEcheances,
                'expanded' => true,
                'multiple' => true,
                'label' => 'Elections',
            ))
            ->add('comparaison', 'entity', array(
                'class' => 'AppBundle\Domain\Election\Entity\Echeance\Echeance',
                'choices' => $this->allEcheances,
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'empty_value' => 'Comparer chaque élection par rapport à la précédente.',
                'label' => 'Point de comparaison',
            ))
            ->getForm()
        ;
    }

    public function __construct(array $allEcheances)
    {
        $this->allEcheances = $allEcheances;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('csrf_protection' => false));
    }

    public function getName()
    {
        return 'echeance_choice';
    }
}
