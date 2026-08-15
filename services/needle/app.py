"""Small HTTP adapter that exposes Needle's schema-constrained complete() API.

College360 executes calls itself; this process only selects a tool and extracts
arguments. Keep it private (127.0.0.1 or an internal network), never public.
"""

from typing import Any

import needle
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field

app = FastAPI(title="College360 Needle", docs_url=None, redoc_url=None)


class CompletionRequest(BaseModel):
    query: str = Field(min_length=1, max_length=4000)
    tools: list[dict[str, Any]] = Field(min_length=1)
    system: str | None = Field(default=None, max_length=1000)


@app.get("/health")
def health() -> dict[str, bool]:
    return {"ok": True}


@app.post("/complete")
def complete(request: CompletionRequest) -> dict[str, Any]:
    try:
        # A new agent binds the exact schemas supplied for this request. This
        # ensures the grammar cannot emit a tool or argument the PHP app did not
        # explicitly permit.
        agent = needle.Needle(tools=request.tools, system=request.system)
        return agent.complete(request.query)
    except Exception as error:  # noqa: BLE001 - keep the HTTP contract stable
        raise HTTPException(status_code=503, detail="Needle inference unavailable") from error
