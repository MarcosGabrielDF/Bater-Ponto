FROM python:3.11-slim

WORKDIR /app

COPY ./backend-python/ /app/

RUN pip install --no-cache-dir -r requirements.txt

EXPOSE 5000

CMD ["python", "app.py"]
