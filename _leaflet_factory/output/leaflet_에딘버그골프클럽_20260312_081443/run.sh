#!/bin/bash
export GEMINI_API_KEY='AIzaSyDEhBr6qsFfczgkO5Hg9UVWBJoYrSIi_dQ'
export PYTHONPATH='/home/ysung/.local/lib/python3.12/site-packages:$PYTHONPATH'
nohup python3 '/var/www/html/_leaflet_factory/scripts/orchestrator.py' --workdir '/var/www/html/_leaflet_factory/output/leaflet_에딘버그골프클럽_20260312_081443' > '/var/www/html/_leaflet_factory/output/leaflet_에딘버그골프클럽_20260312_081443/process.log' 2>&1 &
