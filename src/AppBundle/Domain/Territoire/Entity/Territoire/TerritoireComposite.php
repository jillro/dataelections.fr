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

namespace AppBundle\Domain\Territoire\Entity\Territoire;

class TerritoireComposite extends AbstractTerritoire
{
    const UNION = 1;
    const INTERSECTION = 2;

    /**
     * Le premier territoire composant le territoire composite.
     *
     * @var AbstractTerritoire
     */
    private $territoire1;

    /**
     * Le second territoire composant le territoire composite.
     *
     * @var AbstractTerritoire
     */
    private $territoire2;

    /**
     * Constructeur d'objet TerritoireComposite.
     *
     * @param const              $type        TerritoireComposite::INTERSECTION
     *                                        ou TerritoireComposite::UNION
     * @param AbstractTerritoire $territoire1 Le premier territoire.
     * @param AbstractTerritoire $territoire2 Le second territoire.
     */
    public function __construct(
        $type,
        AbstractTerritoire $territoire1,
        AbstractTerritoire $territoire2
    ) {
        if (!in_array($type, array(self::UNION, self::INTERSECTION), true)) {
            throw new \InvalidArgumentException();
        }

        $this->type = $type;
        $this->territoire1 = $territoire1;
        $this->territoire2 = $territoire2;
    }

    /**
     * Récupérer les territoires composant le territoire composite.
     *
     * @return AbstractTerritoire Les territoires composant l'instance.
     */
    public function getTerritoires()
    {
        return array($this->territoire1, $this->territoire2);
    }
}
