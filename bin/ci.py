#!/usr/bin/env python3

import click
import os
import subprocess
import sys
import time
from rich.console import Console
from rich.markdown import Markdown

console = Console()
script_directory = os.path.dirname(__file__)
working_directory = os.path.abspath(os.path.join(script_directory, '..'))
os.chdir(working_directory)
os.chdir(working_directory)


@click.command()
@click.option('--verbose/--silent', default=False, help='Do you want verbose output of each check?', type=bool)
@click.option('--install/--no-install', default=True, help='Do you want install composer packages in build process?',
              type=bool)
@click.option('--teardown/--no-teardown', default=False, help='Do you want install composer packages in build process?',
              type=bool)
def ci(verbose, install, teardown):
    """This script will run CI scripts and check the project."""
    start = time.time()
    console.print(Markdown('# Running CI for runopencode/query-resources-loader-bundle application.'), width=80, style="green")
    stdout = sys.stdout if verbose else subprocess.PIPE
    container = __spawn_containers(verbose, stdout, teardown)
    ci_result = 0

    commands = {
        'composer install': ('XDEBUG_MODE=off composer install', False),
        'composer run phpunit': ('XDEBUG_MODE=coverage composer run phpunit', False),    
        'composer run php-cs-fixer': ('composer run php-cs-fixer', False),
        'composer run phpmd': ('composer run phpmd', False),
        'composer run phpstan': ('composer run phpstan', False),
        'composer run psalm': ('composer run psalm', False),
        'composer run composer-require-checker': ('composer run composer-require-checker', False),
        'composer run composer-unused': ('composer run composer-unused', False)
    }

    if not install:
        del commands['composer install']

    for name, (command, warn_only) in commands.items():
        console.print('Running `{}`...'.format(name), width=80, style="blue")
        result = subprocess.run(
            'docker exec -w /var/www/html {} {} sh -c "{}"{}'.format(
                sys.__stdin__.isatty() and '-it' or '-t',
                container,
                command,
                ' > /dev/null' if not verbose else ''
            ),
            shell=True,
            stdout=stdout
        )

        failed = 0 != result.returncode

        if failed and warn_only:
            console.print('Warning.', width=80, style="yellow")
            continue

        ci_result = ci_result + result.returncode

        if failed:
            console.print('Failed.', width=80, style="red")
            continue

        console.print('Pass.', width=80, style="green")

    elapsed = round(time.time() - start)

    if 0 == ci_result:
        console.print(Markdown('***'), width=80)
        console.print(
            'SUCCESS! All checks pass, execution time was {} seconds.'.format(elapsed),
            width=80,
            style="green"
        )
        console.print(Markdown('***'), width=80)
        __teardown_containers(verbose) if teardown else None
        exit(0)

    console.print('ERROR! Not all checks passed, execution time was {} seconds.'.format(elapsed), width=80, style="red")
    __teardown_containers(verbose) if teardown else None
    exit(1)


def __spawn_containers(verbose, stdout, teardown):
    result = subprocess.run(
        'docker compose -f docker-compose.yaml ps -q php.local',
        stdout=subprocess.PIPE,
        shell=True
    )

    container = result.stdout.decode('utf-8').strip()

    if '' != container:
        return container 
    
    result = subprocess.run(
        'docker compose -f docker-compose.yaml up --build -d',
        stdout=stdout,
        shell=True
    )

    if 0 != result.returncode:
        console.print(
            'It seams that it is impossible to start docker containers.',
            width=80,
            style="red"
        )
        __teardown_containers(verbose) if teardown else None
        exit(1)

    result = subprocess.run(
        'docker compose -f docker-compose.yaml ps -q php.local',
        stdout=subprocess.PIPE,
        shell=True
    )

    container = result.stdout.decode('utf-8').strip()

    if '' == container:
        console.print(
            'It seams that services are not running, have you even started them?',
            width=80,
            style="red"
        )
        __teardown_containers(verbose) if teardown else None
        exit(1)

    return container


def __teardown_containers(verbose):
    result = subprocess.run(
        'docker compose -f docker-compose.yaml down',
        stdout=subprocess.PIPE if verbose else None,
        shell=True
    )

    if 0 != result.returncode:
        console.print(
            'Unable to teardown containers.',
            width=80,
            style="red"
        )
        exit(1)


if __name__ == '__main__':
    ci()
