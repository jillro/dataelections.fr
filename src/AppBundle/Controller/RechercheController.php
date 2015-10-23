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

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RechercheController extends Controller
{
    /**
     * @Route("/json/rechercher/{terme}", name="rechercher_json")
     */
    public function rechercherJsonAction(Request $request, $terme = null)
    {
        $response = new Response();
        $response->setLastModified($this->get('repository.cache_info')->getCacheInvalidateDate());
        $response->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        }

        $territoires = array();
        if (!empty($terme)) {
            $territoires = $this
                ->get('repository.territoire')
                ->findLike($terme, 20);
        }

        return $this->render(
            'rechercher.json.twig',
            array('territoires' => $territoires),
            $response
        );
    }

    /**
     * @Route("/rechercher/{terme}", name="rechercher")
     * @Method({"GET"})
     */
    public function rechercherAction(Request $request, $terme = null)
    {
        $response = new Response();
        $response->setLastModified($this->get('repository.cache_info')->getCacheInvalidateDate());
        $response->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        }

        $territoires = array();
        if (!empty($terme)) {
            $territoires = $this
                ->get('repository.territoire')
                ->findLike($terme, 90);
        }

        $form = $this->createFormBuilder(array())
            ->setAction('#')
            ->add(
                'terme',
                'text',
                array(
                    'label' => 'Rechercher un territoire '
                            .'(commune, département, région...) : ',
                )
            )
            ->add('Rechercher', 'submit')
            ->getForm();

        return $this->render('rechercher.html.twig', array(
            'form' => $form->createView(),
            'territoires' => $territoires,
        ), $response);
    }
}
