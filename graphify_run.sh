#!/bin/bash
cd "$(dirname "$0")" # Automatically navigate to the directory where this script resides
export OPENAI_API_KEY="sk-nry-vyzy2_hH680jV_S0s6uIu1h0IgbuFUwUqcqiccxtyOw"
export OPENAI_BASE_URL="https://router.bynara.id/v1"
export OPENAI_MODEL="tencent-hy3"

graphify extract . --backend openai
