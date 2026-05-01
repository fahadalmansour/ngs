#!/usr/bin/env python3
"""Create a focused queue for filling missing supplier prices."""

from __future__ import annotations

import argparse
import csv
from pathlib import Path
from typing import Any


DEFAULT_SUPPLIER_MATRIX = Path("output/spreadsheet/supplier_sourcing_matrix.csv")
DEFAULT_EXCEPTIONS = Path("output/spreadsheet/price_floor_exceptions.csv")
DEFAULT_OUTPUT = Path("output/spreadsheet/supplier_price_work_queue.csv")

OUTPUT_COLUMNS = [
    "Priority",
    "Risk",
    "Exception Status",
    "Action",
    "ID",
    "SKU",
    "Product Name",
    "Category",
    "Current Effective Sell Price SAR",
    "Supplier Rank",
    "Supplier Name",
    "Supplier Search URL",
    "Supplier Buy URL",
    "Supplier Price",
    "Currency",
    "Price Source",
    "Notes",
]

RISK_PRIORITY = {
    "High": 1,
    "Medium": 2,
    "Low": 3,
}

SUPPLIER_PRIORITY = {
    "AliExpress": 1,
    "Alibaba": 2,
    "Amazon.sa": 3,
    "Noon": 4,
    "B&H Photo": 5,
}


def clean(value: Any) -> str:
    if value is None:
        return ""
    return " ".join(str(value).split())


def read_csv(path: Path) -> list[dict[str, str]]:
    with path.open(newline="", encoding="utf-8-sig") as handle:
        return list(csv.DictReader(handle))


def write_csv(path: Path, rows: list[dict[str, str]]) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open("w", newline="", encoding="utf-8-sig") as handle:
        writer = csv.DictWriter(handle, fieldnames=OUTPUT_COLUMNS)
        writer.writeheader()
        writer.writerows(rows)


def make_queue(supplier_rows: list[dict[str, str]], exception_rows: list[dict[str, str]]) -> list[dict[str, str]]:
    exceptions = {clean(row["SKU"]): row for row in exception_rows}
    queue: list[dict[str, str]] = []

    for supplier in supplier_rows:
        sku = clean(supplier.get("SKU"))
        exception = exceptions.get(sku)
        if not exception:
            continue

        has_price = bool(clean(supplier.get("Supplier Price")))
        action = "Already has supplier/reference price" if has_price else "Open search URL, pick exact listing, fill Buy URL + Supplier Price"
        risk = clean(exception.get("Risk"))
        priority = RISK_PRIORITY.get(risk, 9)

        queue.append(
            {
                "Priority": str(priority),
                "Risk": risk,
                "Exception Status": clean(exception.get("Status")),
                "Action": action,
                "ID": clean(supplier.get("ID")),
                "SKU": sku,
                "Product Name": clean(supplier.get("Product Name")),
                "Category": clean(supplier.get("Category")),
                "Current Effective Sell Price SAR": clean(exception.get("Effective sell price SAR")),
                "Supplier Rank": clean(supplier.get("Supplier Rank")),
                "Supplier Name": clean(supplier.get("Supplier Name")),
                "Supplier Search URL": clean(supplier.get("Supplier Search URL")),
                "Supplier Buy URL": clean(supplier.get("Supplier Buy URL")),
                "Supplier Price": clean(supplier.get("Supplier Price")),
                "Currency": clean(supplier.get("Currency")),
                "Price Source": clean(supplier.get("Price Source")),
                "Notes": clean(supplier.get("Notes")),
            }
        )

    queue.sort(
        key=lambda row: (
            int(row["Priority"]),
            clean(row["SKU"]),
            0 if not row["Supplier Price"] else 1,
            SUPPLIER_PRIORITY.get(row["Supplier Name"], 99),
        )
    )
    return queue


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Generate a prioritized supplier price entry queue.")
    parser.add_argument("--supplier-matrix", type=Path, default=DEFAULT_SUPPLIER_MATRIX)
    parser.add_argument("--exceptions", type=Path, default=DEFAULT_EXCEPTIONS)
    parser.add_argument("--output", type=Path, default=DEFAULT_OUTPUT)
    return parser.parse_args()


def main() -> None:
    args = parse_args()
    supplier_rows = read_csv(args.supplier_matrix)
    exception_rows = read_csv(args.exceptions)
    queue = make_queue(supplier_rows, exception_rows)
    write_csv(args.output, queue)

    high = sum(1 for row in queue if row["Risk"] == "High")
    missing = sum(1 for row in queue if not row["Supplier Price"])
    print(f"Generated {args.output} with {len(queue)} supplier rows.")
    print(f"High-risk rows: {high}; rows still missing supplier price: {missing}.")


if __name__ == "__main__":
    main()
