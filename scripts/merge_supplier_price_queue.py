#!/usr/bin/env python3
"""Merge filled supplier price queue rows back into the full supplier matrix."""

from __future__ import annotations

import argparse
import csv
from pathlib import Path
from typing import Any


DEFAULT_SUPPLIER_MATRIX = Path("output/spreadsheet/supplier_sourcing_matrix.csv")
DEFAULT_QUEUE = Path("output/spreadsheet/supplier_price_work_queue.csv")
DEFAULT_OUTPUT = Path("output/spreadsheet/supplier_sourcing_matrix_filled.csv")

UPDATE_COLUMNS = [
    "Supplier Buy URL",
    "Supplier Price",
    "Currency",
    "Price Source",
    "Notes",
]


def clean(value: Any) -> str:
    if value is None:
        return ""
    return " ".join(str(value).split())


def read_csv(path: Path) -> tuple[list[str], list[dict[str, str]]]:
    with path.open(newline="", encoding="utf-8-sig") as handle:
        reader = csv.DictReader(handle)
        return list(reader.fieldnames or []), list(reader)


def write_csv(path: Path, fieldnames: list[str], rows: list[dict[str, str]]) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open("w", newline="", encoding="utf-8-sig") as handle:
        writer = csv.DictWriter(handle, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(rows)


def row_key(row: dict[str, str]) -> tuple[str, str]:
    return clean(row.get("SKU")), clean(row.get("Supplier Name"))


def merge_rows(matrix_rows: list[dict[str, str]], queue_rows: list[dict[str, str]]) -> tuple[list[dict[str, str]], int]:
    filled_queue = {
        row_key(row): row
        for row in queue_rows
        if clean(row.get("Supplier Price")) or clean(row.get("Supplier Buy URL"))
    }

    updates = 0
    merged: list[dict[str, str]] = []
    for row in matrix_rows:
        updated = dict(row)
        queue_row = filled_queue.get(row_key(row))
        if queue_row:
            changed = False
            for column in UPDATE_COLUMNS:
                value = clean(queue_row.get(column))
                if value and clean(updated.get(column)) != value:
                    updated[column] = value
                    changed = True
            if changed:
                updates += 1
        merged.append(updated)

    return merged, updates


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Merge filled supplier queue values into supplier_sourcing_matrix.csv.")
    parser.add_argument("--supplier-matrix", type=Path, default=DEFAULT_SUPPLIER_MATRIX)
    parser.add_argument("--queue", type=Path, default=DEFAULT_QUEUE)
    parser.add_argument("--output", type=Path, default=DEFAULT_OUTPUT)
    return parser.parse_args()


def main() -> None:
    args = parse_args()
    matrix_columns, matrix_rows = read_csv(args.supplier_matrix)
    _, queue_rows = read_csv(args.queue)
    merged, updates = merge_rows(matrix_rows, queue_rows)
    if updates == 0:
        print(f"No filled queue values found. Skipped {args.output} to avoid a duplicate matrix.")
        return

    write_csv(args.output, matrix_columns, merged)
    print(f"Wrote {args.output} with {updates} updated supplier rows.")


if __name__ == "__main__":
    main()
