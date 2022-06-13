package pl.lukaszgorczyk.coachassistant.model;

import com.fasterxml.jackson.annotation.JsonFormat;

import javax.persistence.*;
import javax.validation.constraints.NotBlank;
import java.lang.annotation.Target;
import java.time.LocalDateTime;

@Entity
@Table(name = "players")
public class Player {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private int id;
    @NotBlank(message = "Name must be not null or empty")
    private String name;
    @NotBlank(message = "Surname must be not null or empty")
    private String surname;
    @Column(name = "birth_date")
    @JsonFormat(pattern="YYYY-MM-dd")
    private LocalDateTime birthDate;
    private String footed;
    private String position;

    public Player() {
    }

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getSurname() {
        return surname;
    }

    public void setSurname(String surname) {
        this.surname = surname;
    }

    public LocalDateTime getBirthDate() {
        return birthDate;
    }

    public void setBirthDate(LocalDateTime birthDate) {
        this.birthDate = birthDate;
    }

    public String getFooted() {
        return footed;
    }

    public void setFooted(String footed) {
        this.footed = footed;
    }

    public String getPosition() {
        return position;
    }

    public void setPosition(String position) {
        this.position = position;
    }
}
