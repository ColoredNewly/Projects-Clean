from turtle import Turtle

class Paddle(Turtle):
    def init(self):
        super().init()
        self.shape("square")
        self.shapesize(stretch_wid=5, stretch_len=1)
        self.penup()
        self.goto(x=350,y=0)
        self.color("white")

    def go_up(self):
        new_y=self.ycor()+20
        self.goto(self.xcor(),new_y)

    def go_down(self):
        new_y=self.ycor()-20
        self.goto(self.xcor(), new_y)