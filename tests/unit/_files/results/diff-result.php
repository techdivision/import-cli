<?php
/**
 * Copyright (c) 2023 TechDivision GmbH
 * All rights reserved
 *
 * This product includes proprietary software developed at TechDivision GmbH, Germany
 * For more information see https://www.techdivision.com/
 *
 * To obtain a valid license for using this software please contact us at
 * license@techdivision.com
 */

return [
    'operations/general/general/create-ok-files/plugins/create-ok-files/id' => 'import.plugin.create.ok.files',
    'operations/general/general/global-data/plugins/global-data/id' => 'import.plugin.global.data',
    'operations/general/general/initialize/name' => 'initialize',
    'operations/general/general/initialize/plugins/initialize/id' => 'import.plugin.initialize',
    'operations/general/general/initialize/plugins/initialize/listeners/0/plugin.process.success/0' => 'import.listener.render.ansi.art',
    'operations/general/general/initialize/plugins/initialize/listeners/0/plugin.process.success/1' => 'import.listener.render.operation.info',
    'operations/general/general/initialize/plugins/initialize/listeners/0/plugin.process.success/2' => 'import.listener.render.debug.info',
    'operations/general/general/initialize/plugins/initialize/listeners/0/plugin.process.success/3' => 'import.listener.initialize.registry',
    'operations/general/general/clean-up/plugins/clean-up/id' => 'import.plugin.clean.up',
    'operations/general/general/clean-up/plugins/clean-up/listeners/0/plugin.process.success/0' => 'import.listener.finalize.registry',
    'operations/general/general/clean-up/plugins/clean-up/listeners/0/plugin.process.success/1' => 'import.listener.archive',
    'operations/general/general/clean-up/plugins/clean-up/listeners/0/plugin.process.success/2' => 'import.listener.clear.artefacts',
    'operations/general/general/clean-up/plugins/clean-up/listeners/0/plugin.process.success/3' => 'import.listener.clear.directories',
    'operations/general/general/clean-up/plugins/clean-up/listeners/0/plugin.process.success/4' => 'import.listener.import.history',
    'operations/general/general/clean-up/plugins/clean-up/listeners/0/plugin.process.success/5' => 'import.listener.clear.registry',
    'operations/general/general/move-files/plugins/subject/id' => 'import.plugin.subject',
    'operations/general/general/move-files/plugins/subject/listeners/0/plugin.process.success/0' => 'import.listener.reset.logger',
    'operations/general/general/move-files/plugins/subject/subjects/0/id' => 'import.subject.move.files',
    'operations/general/general/move-files/plugins/subject/subjects/0/file-resolver/id' => 'import.subject.file.resolver.move.files',
    'operations/general/general/move-files/plugins/subject/subjects/0/ok-file-needed' => true
];
