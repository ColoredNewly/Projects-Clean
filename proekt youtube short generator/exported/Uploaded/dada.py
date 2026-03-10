from turtle import Screen
from padle import Paddle

screen=Screen()
screen.bgcolor("black")
screen.setup(width=800, height=600)
screen.title("Pong")

paddle = Paddle()


game_is_on=True

while game_is_on:
    screen.listen()
    screen.onkey(paddle.go_up,"Up")
    screen.onkey(paddle.go_down, "Down")

screen.exitonclick()