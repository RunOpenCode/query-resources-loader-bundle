#!/usr/bin/env python3

import click
import os
import subprocess
import sys
from rich.console import Console
from rich.markdown import Markdown

console = Console()
script_directory = os.path.dirname(__file__)
working_directory = os.path.abspath(os.path.join(script_directory, '..'))
os.chdir(working_directory)


@click.command()
@click.option('--verbose/--silent', default=False, help='Do you want verbose output of this command.', type=bool)
def shutdown(verbose):
    """This script will tear down all services and remove all containers."""
    console.print(
        Markdown('# Tearing down runopencode/query-resources-loader-bundle development environment...'),
        width=80,
        style="green"
    )

    result = subprocess.run(
        'docker compose -f docker-compose.yaml down {}'.format(' > /dev/null' if not verbose else ''),
        shell=True,
        stdout=(sys.stdout if verbose else subprocess.PIPE)
    )

    if 0 != result.returncode:
        console.print('🐳 ERROR! Unable to teardown runopencode/query-resources-loader-bundle development environment!', width=80, style="red")
        exit(1)

    console.print(Markdown('***'), width=80, style="green")
    console.print('runopencode/query-resources-loader-bundle development environment destroyed.', width=80, style="green")
    console.print(Markdown('***'), width=80, style="green")


if __name__ == '__main__':
    shutdown()
