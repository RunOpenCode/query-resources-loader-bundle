#!/usr/bin/env python3

import os
import subprocess
import sys

import click
from rich.console import Console
from rich.markdown import Markdown

console = Console()
script_directory = os.path.dirname(__file__)
working_directory = os.path.abspath(os.path.join(script_directory, '..'))
monorepo_directory = os.path.abspath(os.path.join(script_directory, '..', '..', '..', '..'))
os.chdir(working_directory)


@click.command()
@click.option('--install/--no-install', default=True,
              help='Do you want to install project dependencies with environment start?', type=bool)
@click.option('--verbose/--silent', default=False, help='Do you want verbose output of this command?', type=bool)
def run(install, verbose):
    """This script spawns containers and optionally installs dependencies."""
    console.print(
        Markdown('# Starting runopencode/query-resources-loader-bundle development environment...'),
        width=80,
        style="green"
    )

    result = subprocess.run(
        'docker compose -f docker-compose.yaml up -d --build {}'.format(' > /dev/null' if not verbose else ''),
        shell=True,
        stdout=(sys.stdout if verbose else subprocess.PIPE)
    )

    if 0 != result.returncode:
        console.print('🐳 ERROR! runopencode/query-resources-loader-bundle development environment failed to start!', width=80,
                      style="red")
        exit(1)

    if install:
        __composer_install(verbose)

    console.print(Markdown('***'), width=80, style="green")
    console.print('🐳 SUCCESS! runopencode/query-resources-loader-bundle development environment is up and running!', width=80, style="green")
    console.print(Markdown('***'), width=80, style="green")


def __composer_install(verbose):
    result = subprocess.run(
        'docker compose -f docker-compose.yaml ps -q php.local',
        stdout=subprocess.PIPE,
        shell=True
    )

    console.print('Installing project dependencies...', width=80, style="blue")

    container = result.stdout.decode('utf-8').strip()

    result = subprocess.run(
        'docker exec -w /var/www/html -it {} sh -c "composer install"{}'
        .format(container, ' > /dev/null' if not verbose else ''),
        shell=True,
        stdout=sys.stdout if verbose else subprocess.PIPE
    )

    if 0 != result.returncode:
        console.print(
            'It seams that it is impossible install project dependencies, try installing manually...',
            width=80,
            style="red"
        )
        return

    console.print('Project dependencies successfully installed.', width=80, style="green")


if __name__ == '__main__':
    run()
