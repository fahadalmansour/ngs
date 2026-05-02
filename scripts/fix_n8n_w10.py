#!/usr/bin/env python3
"""
Fix W10 'Send Photo to Channel' HTTP node in n8n.

Problem: URL uses $credentials.telegramApi.accessToken which HTTP Request
         nodes cannot resolve — causes runtime error.
Fix:     Replace with hardcoded bot token URL.

Run from CT119 (n8n Docker host) or any host with access to 192.168.8.131:5678.
Requires: python3, requests (pip install requests)
"""
import json
import sqlite3
import sys

N8N_DB = '/var/lib/docker/volumes/n8n_n8n_data/_data/database.sqlite'
WORKFLOW_ID = 'JguJID4lakr3tIbu'
BOT_TOKEN = '8768777836:AAF_2g_r8FbDll_B9m_h4gqkp2C7GcoXqsg'
FIXED_URL = f'https://api.telegram.org/bot{BOT_TOKEN}/sendPhoto'

def fix_via_sqlite():
    """Direct SQLite fix — most reliable approach since n8n API PUT has strict schema."""
    conn = sqlite3.connect(N8N_DB)
    conn.row_factory = sqlite3.Row

    row = conn.execute(
        "SELECT id, name, nodes FROM workflow_entity WHERE id=?",
        [WORKFLOW_ID]
    ).fetchone()

    if not row:
        print(f"ERROR: Workflow {WORKFLOW_ID} not found in database")
        conn.close()
        sys.exit(1)

    print(f"Found workflow: {row['name']}")
    nodes = json.loads(row['nodes'])

    fixed = False
    for node in nodes:
        if node.get('name') == 'Send Photo to Channel' and node.get('type') == 'n8n-nodes-base.httpRequest':
            params = node.get('parameters', {})

            # Fix the URL
            old_url = params.get('url', '')
            params['url'] = FIXED_URL
            print(f"URL: {old_url!r} -> {FIXED_URL!r}")

            # Fix jsonBody if it uses template literals or $credentials
            body = params.get('jsonBody', '')
            if '$credentials' in str(body) or '$json' not in str(body):
                params['jsonBody'] = (
                    '={\n'
                    '  "chat_id": "{{ $json.chatId }}",\n'
                    '  "photo": "{{ $json.imageUrl }}",\n'
                    '  "caption": "{{ $json.caption }}"\n'
                    '}'
                )
                print(f"Fixed jsonBody expression")

            node['parameters'] = params
            fixed = True
            break

    if not fixed:
        print("ERROR: 'Send Photo to Channel' node not found or already fixed")
        conn.close()
        sys.exit(1)

    conn.execute(
        "UPDATE workflow_entity SET nodes=?, updatedAt=datetime('now') WHERE id=?",
        [json.dumps(nodes), WORKFLOW_ID]
    )
    conn.commit()
    conn.close()
    print(f"SUCCESS: W10 fixed in SQLite")
    print(f"Restart n8n to pick up changes:")
    print(f"  docker restart n8n")


if __name__ == '__main__':
    print(f"Fixing W10 workflow {WORKFLOW_ID}...")
    fix_via_sqlite()
