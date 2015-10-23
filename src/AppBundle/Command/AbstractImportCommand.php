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

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractImportCommand extends ContainerAwareCommand
{
    /**
     * Input.
     *
     * @var InputInterface
     */
    protected $input;

    /**
     * Output.
     *
     * @var OutputInterface
     */
    protected $output;

    protected function countLines($fileHandle)
    {
        $i = 0;
        while (!feof($fileHandle)) {
            fgets($fileHandle);
            $i++;
        }
        rewind($fileHandle);

        return $i;
    }

    protected function getFileHandle()
    {
        $file = $this->input->getArgument('file');

        $fileHandle = @fopen($file, 'r');
        if (!$fileHandle) {
            throw new \RuntimeException('Fichier non trouvé.');
        }
        $this->output->writeln('Lecture du fichier...');

        return $fileHandle;
    }
}
