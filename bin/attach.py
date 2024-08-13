#!/usr/bin/env python3

import os
import subprocess

import click
from rich.console import Console

console = Console()
script_directory = os.path.dirname(__file__)
working_directory = os.path.abspath(os.path.join(script_directory, '..'))
os.chdir(working_directory)


@click.command()
@click.option('--service', default='php.local', help='Name of the service on which you want to attach.')
@click.option('--command', default='/bin/bash', help='A command to execute when attaching to container.')
def attach(service, command):
    """This script will attach to container of the service defined in `docker-compose.yaml` file."""
    console.print(
        'Leaving host environment and attaching to service {}...'.format(service),
        width=80,
        style="yellow"
    )

    result = subprocess.run(
        'docker compose -f docker-compose.yaml ps -q {}'.format(service),
        stdout=subprocess.PIPE,
        shell=True
    )

    container = result.stdout.decode('utf-8').strip()

    if '' == container:
        console.print(
            'Service "{}" is not running, have you even started it?'.format(service),
            width=80,
            style="red"
        )
        exit(1)

    os.system('docker exec -it {} /bin/bash -c "{}"'.format(container, command))


if __name__ == '__main__':
    attach()
